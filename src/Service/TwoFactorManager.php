<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage\Service;

use Neo\Core\Database\ORM\Persistence\EntityManager;
use Vendor\NeoPHP\TwoFactorPackage\Database\Entity\TwoFactorSecret;
use Vendor\NeoPHP\TwoFactorPackage\Database\Repository\TwoFactorSecretRepository;

final class TwoFactorManager
{
    /** @var list<array{action: 'confirm'|'verify', userType: string, userId: int, success: bool, reason: string|null}> */
    private static array $checks = [];

    public function __construct(
        private readonly EntityManager $em,
        private readonly TotpManager $totp,
    ) {}

    public function setupFor(string $userType, int $userId): TwoFactorSecret
    {
        $existing = $this->repository()->findForUser($userType, $userId);

        if ($existing !== null) {
            return $existing;
        }

        $secret = new TwoFactorSecret($userType, $userId, $this->totp->generateSecret());
        $this->em->persist($secret);
        $this->em->flush();

        return $secret;
    }

    public function isEnabledFor(string $userType, int $userId): bool
    {
        $secret = $this->repository()->findForUser($userType, $userId);

        return $secret !== null && $secret->isEnabled();
    }

    public function confirmAndEnable(string $userType, int $userId, string $code): bool
    {
        $secret = $this->repository()->findForUser($userType, $userId);

        if ($secret === null) {
            self::$checks[] = [
                'action' => 'confirm',
                'userType' => $userType,
                'userId' => $userId,
                'success' => false,
                'reason' => 'No secret set up for this user',
            ];

            return false;
        }

        if (!$this->totp->verify($secret->getSecret(), $code)) {
            self::$checks[] = [
                'action' => 'confirm',
                'userType' => $userType,
                'userId' => $userId,
                'success' => false,
                'reason' => 'Invalid TOTP code',
            ];

            return false;
        }

        $secret->enable();
        $this->em->flush();

        self::$checks[] = [
            'action' => 'confirm',
            'userType' => $userType,
            'userId' => $userId,
            'success' => true,
            'reason' => null,
        ];

        return true;
    }

    public function verifyCode(string $userType, int $userId, string $code): bool
    {
        $secret = $this->repository()->findForUser($userType, $userId);

        if ($secret === null || !$secret->isEnabled()) {
            self::$checks[] = [
                'action' => 'verify',
                'userType' => $userType,
                'userId' => $userId,
                'success' => false,
                'reason' => $secret === null ? 'No secret set up for this user' : 'Two-factor not enabled for this user',
            ];

            return false;
        }

        $success = $this->totp->verify($secret->getSecret(), $code);

        self::$checks[] = [
            'action' => 'verify',
            'userType' => $userType,
            'userId' => $userId,
            'success' => $success,
            'reason' => $success ? null : 'Invalid TOTP code',
        ];

        return $success;
    }

    public function disableFor(string $userType, int $userId): void
    {
        $secret = $this->repository()->findForUser($userType, $userId);

        if ($secret !== null) {
            $this->em->remove($secret);
            $this->em->flush();
        }
    }

    public function getQrCodeUrlFor(string $userType, int $userId, string $label, string $issuer = 'NeoPHP'): ?string
    {
        $secret = $this->repository()->findForUser($userType, $userId);

        if ($secret === null) {
            return null;
        }

        return $this->totp->getQrCodeUrl($secret->getSecret(), $label, $issuer);
    }

    private function repository(): TwoFactorSecretRepository
    {
        /** @var TwoFactorSecretRepository $repo */
        $repo = $this->em->getRepository(TwoFactorSecret::class);

        return $repo;
    }

    /**
     * @return list<array{action: 'confirm'|'verify', userType: string, userId: int, success: bool, reason: string|null}>
     */
    public static function getChecks(): array
    {
        return self::$checks;
    }
}