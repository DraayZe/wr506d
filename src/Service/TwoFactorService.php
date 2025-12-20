<?php

namespace App\Service;

use App\Entity\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;
use RuntimeException;


class TwoFactorService
{
    private string $issuer;

    public function __construct(string $appName = 'MyApp')
    {
        $this->issuer = $appName;
    }

    /**
     * Generate a new TOTP secret for a user
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function generateSecret(): string
    {
        $totp = TOTP::generate();
        return $totp->getSecret();
    }

    /**
     * Get TOTP instance for a user
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getTOTP(User $user): TOTP
    {
        $secret = $user->getTwoFactorSecret();
        if ($secret === null) {
            throw new RuntimeException('User does not have a 2FA secret');  // ← CHANGÉ
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

        $qrCode = new QrCode($provisioningUri);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
    /**
     * Verify TOTP code with time window tolerance
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function verifyCode(User $user, string $code): bool
    {
        $secret = $user->getTwoFactorSecret();
        if (!$secret) {
            return false;
        }

        $totp = TOTP::createFromSecret($secret);
        $totp->setIssuer($this->issuer);

        // Vérifie le code avec une fenêtre de ±1 (tolérance de 30 sec avant/après)
        return $totp->verify($code, null, 1);
    }

    /**
     * Generate backup codes
     * @return list<string>
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            // Generate 8-character alphanumeric codes
            $codes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }
        return $codes;
    }

    /**
     * Hash backup codes for storage
     * @param list<string> $codes
     * @return list<string>
     */
    public function hashBackupCodes(array $codes): array
    {
        return array_map(fn($code) => hash('sha256', $code), $codes);
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $hashedCode = hash('sha256', $code);
        $backupCodes = $user->getTwoFactorBackupCodes();

        if ($backupCodes === null) {
            return false;
        }

        return in_array($hashedCode, $backupCodes, true);
    }

    /**
     * Remove used backup code
     */
    public function removeBackupCode(User $user, string $code): void
    {
        $hashedCode = hash('sha256', $code);
        $backupCodes = $user->getTwoFactorBackupCodes();

        if ($backupCodes === null) {
            return;
        }

        $filtered = array_filter($backupCodes, fn($backupCode) => $backupCode !== $hashedCode);  // ← CHANGÉ
        $user->setTwoFactorBackupCodes(array_values($filtered));
    }
}
