<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Review;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

final class ReviewSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['setUserForReview', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function setUserForReview(ViewEvent $event): void
    {
        $review = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Log pour debug
        $this->logger->info('ReviewSubscriber triggered', [
            'method' => $method,
            'is_review' => $review instanceof Review,
            'user' => $this->security->getUser()?->getUserIdentifier(),
            'auth_header' => $event->getRequest()->headers->get('Authorization') ? 'Present' : 'Missing'
        ]);

        if (!$review instanceof Review || Request::METHOD_POST !== $method) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user) {
            $this->logger->error('No user found when creating review');
            return;
        }

        // Assigner l'utilisateur automatiquement
        $review->setUser($user);

        $this->logger->info('User assigned to review', [
            'user' => $user->getUserIdentifier()
        ]);
    }
}
