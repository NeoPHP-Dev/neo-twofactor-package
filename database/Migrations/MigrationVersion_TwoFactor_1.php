<?php

declare(strict_types=1);

use Neo\Core\Database\DatabaseManager;
use Neo\Core\Database\Migration\Interface\MigrationInterface;

final class MigrationVersion_TwoFactor_1 implements MigrationInterface
{
    public function up(DatabaseManager $db): void
    {
        $db->execute("
            CREATE TABLE IF NOT EXISTS `neo_two_factor_secrets` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_type` VARCHAR(255) NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `secret` VARCHAR(255) NOT NULL,
                `enabled` TINYINT(1)   NOT NULL DEFAULT 0,
                `recovery_codes` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_two_factor_user` (`user_type`, `user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(DatabaseManager $db): void
    {
        $db->execute("DROP TABLE IF EXISTS `neo_two_factor_secrets`");
    }
}