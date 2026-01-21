<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\StorageInterface;
use Symfony\Component\RateLimiter\Policy\TokenBucketLimiter;
use Symfony\Component\RateLimiter\Policy\Rate;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use DateInterval;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ApiRateLimitSubscriber implements EventSubscriberInterface
{
    private const ONE_MINUTE_INTERVAL = 'PT1M';

    public function __construct(
        private readonly RateLimiterFactory $anonApiLimiter,
        private readonly StorageInterface $rateLimiterStorage,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        if (str_starts_with($request->getPathInfo(), '/api/docs') ||
            str_starts_with($request->getPathInfo(), '/api/graphql/graphiql')) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        $isAuthenticated = $user instanceof UserInterface && $user instanceof User;

        $limiter = $this->createLimiter($isAuthenticated, $user, $event);

        $limit = $limiter->consume();

        $request->attributes->set('_rate_limit', [
            'limit' => $limit->getLimit(),
            'remaining' => $limit->getRemainingTokens(),
            'reset' => $limit->getRetryAfter()->getTimestamp(),
        ]);

        if (!$limit->isAccepted()) {
            $this->handleRateLimitExceeded($event, $limit);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $rateLimitInfo = $request->attributes->get('_rate_limit');
        if (!$rateLimitInfo) {
            return;
        }

        $response->headers->set('X-RateLimit-Limit', (string) $rateLimitInfo['limit']);
        $response->headers->set('X-RateLimit-Remaining', (string) $rateLimitInfo['remaining']);
        $response->headers->set('X-RateLimit-Reset', (string) $rateLimitInfo['reset']);
    }

    private function createLimiter(bool $isAuthenticated, mixed $user, RequestEvent $event): mixed
    {
        if ($isAuthenticated) {
            return $this->createAuthenticatedLimiter($user);
        }

        $identifier = $event->getRequest()->getClientIp() ?? 'unknown';
        return $this->anonApiLimiter->create($identifier);
    }

    private function createAuthenticatedLimiter(User $user): TokenBucketLimiter
    {
        $identifier = $user->getUserIdentifier();

        $userLimit = match (true) {
            in_array('ROLE_ADMIN', $user->getRoles()) => 10000,
            in_array('ROLE_USER', $user->getRoles()) => 100,
            default => $user->getLimiter(),
        };

        $interval = new DateInterval(self::ONE_MINUTE_INTERVAL);
        $rate = new Rate($interval, $userLimit);  // DateInterval d'abord, puis int

        return new TokenBucketLimiter(
            id: 'user_' . $identifier,
            maxBurst: $userLimit,
            rate: $rate,
            storage: $this->rateLimiterStorage
        );
    }

    private function handleRateLimitExceeded(RequestEvent $event, mixed $limit): void
    {
        $retryAfter = $limit->getRetryAfter();
        $response = new JsonResponse(
            [
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter->getTimestamp(),
            ],
            429
        );

        $response->headers->set('Retry-After', (string) $retryAfter->getTimestamp());
        $response->headers->set('X-RateLimit-Limit', (string) $limit->getLimit());
        $response->headers->set('X-RateLimit-Remaining', '0');
        $response->headers->set('X-RateLimit-Reset', (string) $retryAfter->getTimestamp());

        $event->setResponse($response);
    }
}
