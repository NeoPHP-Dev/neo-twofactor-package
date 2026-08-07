<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage\Database\Repository;

use Neo\Core\Database\ORM\Persistence\EntityRepository;
use Vendor\NeoPHP\TwoFactorPackage\Database\Entity\TwoFactorSecret;

/**
 * @extends EntityRepository<TwoFactorSecret>
 */
final class TwoFactorSecretRepository extends EntityRepository
{
    public function findForUser(string $userType, int $userId): ?TwoFactorSecret
    {
        return $this->findOneBy(['userType' => $userType, 'userId' => $userId]);
    }
}