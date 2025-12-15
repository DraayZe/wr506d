<?php

namespace App\Service;

use App\Entity\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;

class TwoFactorService
{
    private string $issuer;

    public function __construct(string $appName = 'MyApp')
    {
        $this->issuer = $appName;
    }

    /**
     * Generate a new TOTP secret for a user
     */
    public function generateSecret(): string
    {
        $totp = TOTP::generate();
        return $totp->getSecret();
    }

    /**
     * Get TOTP instance for a user
     */
    private function getTOTP(User $user): TOTP
    {
        $secret = $user->getTwoFactorSecret();
        if ($secret === null) {
            throw new \RuntimeException('User does not have a 2FA secret');
        }

        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($user->getEmail() ?? 'user');
        $totp->setIssuer($this->issuer);

        return $totp;
    }

    /**
     * Generate provisioning URI for QR code
     */
    public function getProvisioningUri(User $user): string
    {
        return $this->getTOTP($user)->getProvisioningUri();
    }

    /**
     * Generate QR code as base64 image data
     */
    public function getQrCode(User $user): string
    {
        $provisioningUri = $this->getProvisioningUri($user);

        // Génération du QR code
        $qrCode = new QrCode($provisioningUri);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    /**
     * Verify TOTP code
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->getTwoFactorSecret()) {
            return false;
        }

        return $this->getTOTP($user)->verify($code);
    }
}
