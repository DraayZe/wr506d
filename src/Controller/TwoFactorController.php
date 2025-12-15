<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TwoFactorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa')]
class TwoFactorController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/setup', name: 'app_2fa_setup', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function setup(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $secret = $this->twoFactorService->generateSecret();
        $user->setTwoFactorSecret($secret);
        $user->setTwoFactorEnabled(false);
        $this->entityManager->flush();

        $qrCodeDataUri = $this->twoFactorService->getQrCode($user);
        $provisioningUri = $this->twoFactorService->getProvisioningUri($user);

        return $this->json([
            'secret' => $secret,
            'qr_code' => $qrCodeDataUri,
            'provisioning_uri' => $provisioningUri,
            'message' => 'Scan this QR code with your authenticator app...',
        ]);
    }
}
