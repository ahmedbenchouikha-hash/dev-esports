<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isUsed field to password_reset_token table and create chat_message table';
    }

    public function up(Schema $schema): void
    {
        // Add isUsed column to password_reset_token if it doesn't exist
        $table = $schema->getTable('password_reset_token');
        if (!$table->hasColumn('is_used')) {
            $this->addSql('ALTER TABLE password_reset_token ADD is_used TINYINT(1) NOT NULL DEFAULT 0');
        }

        // Create chat_message table
        if (!$schema->hasTable('chat_message')) {
            $this->addSql('CREATE TABLE chat_message (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                user_message VARCHAR(500) NOT NULL,
                bot_response LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                category VARCHAR(50) NOT NULL DEFAULT \'general\',
                PRIMARY KEY(id),
                INDEX IDX_FAB3FC16A76ED395 (user_id),
                INDEX idx_user_created_at (user_id, created_at),
                CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('password_reset_token');
        if ($table->hasColumn('is_used')) {
            $this->addSql('ALTER TABLE password_reset_token DROP COLUMN is_used');
        }

        if ($schema->hasTable('chat_message')) {
            $this->addSql('DROP TABLE chat_message');
        }
    }
}
