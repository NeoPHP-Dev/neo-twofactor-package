<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage\Database\Entity;

use Neo\Core\Database\ORM\Mapping\Attribute\Column;
use Neo\Core\Database\ORM\Mapping\Attribute\Entity;
use Neo\Core\Database\ORM\Mapping\Attribute\GeneratedValue;
use Neo\Core\Database\ORM\Mapping\Attribute\Id;
use Neo\Core\Database\ORM\Mapping\Attribute\Table;
use Vendor\NeoPHP\TwoFactorPackage\Database\Repository\TwoFactorSecretRepository;

#[Entity(repositoryClass: TwoFactorSecretRepository::class)]
#[Table(name: 'neo_two_factor_secrets')]
final class TwoFactorSecret
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer', unsigned: true)]
    private ?int $id = null;

    #[Column(type: 'string', name: 'user_type', length: 255)]
    private string $userType;

    #[Column(type: 'integer', name: 'user_id', unsigned: true)]
    private int $userId;

    #[Column(type: 'string', length: 255)]
    private string $secret;

    #[Column(type: 'boolean')]
    private bool $enabled;

    #[Column(type: 'text', name: 'recovery_codes', nullable: true)]
    private ?string $recoveryCodes = null;

    #[Column(type: 'datetime', name: 'created_at')]
    private \DateTime $createdAt;

    public function __construct(string $userType, int $userId, string $secret)
    {
        $this->userType = $userType;
        $this->userId = $userId;
        $this->secret = $secret;
        $this->enabled = false;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getRecoveryCodes(): ?string
    {
        return $this->recoveryCodes;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function setRecoveryCodes(string $codes): void
    {
        $this->recoveryCodes = $codes;
    }
}