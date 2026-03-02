<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Create password_reset_token and chat_message tables
 * (Skipping user/team as they already exist with different schema)
 */
final class Version20260226120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create password_reset_token and chat_message tables for user module integration';
    }

    public function up(Schema $schema): void
    {
        // Create password_reset_token table
        $this->addSql('CREATE TABLE IF NOT EXISTS password_reset_token (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL,
            created_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            is_used TINYINT(1) NOT NULL DEFAULT 0,
            UNIQUE INDEX UNIQ_PASSWORD_RESET_TOKEN_TOKEN (token),
            INDEX IDX_PASSWORD_RESET_TOKEN_USER_ID (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraint for password_reset_token
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_PASSWORD_RESET_TOKEN_USER_ID FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        // Create chat_message table
        $this->addSql('CREATE TABLE IF NOT EXISTS chat_message (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            user_message VARCHAR(500) NOT NULL,
            bot_response LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            category VARCHAR(50) NOT NULL DEFAULT \'general\',
            PRIMARY KEY(id),
            INDEX IDX_FAB3FC16A76ED395 (user_id),
            INDEX idx_user_created_at (user_id, created_at),
            CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS chat_message');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_PASSWORD_RESET_TOKEN_USER_ID');
        $this->addSql('DROP TABLE IF EXISTS password_reset_token');
    }
}
