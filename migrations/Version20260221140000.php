<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260221140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create manager_request table for role management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE manager_request (
            id INT AUTO_INCREMENT NOT NULL,
            player_id INT NOT NULL,
            reviewed_by_id INT NULL,
            team_name VARCHAR(255) NULL,
            motivation LONGTEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "pending",
            created_at DATETIME NOT NULL,
            reviewed_at DATETIME NULL,
            admin_comment LONGTEXT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (player_id) REFERENCES player(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by_id) REFERENCES `user`(id) ON DELETE SET NULL,
            INDEX idx_status (status),
            INDEX idx_player (player_id),
            INDEX idx_created_at (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS manager_request');
    }
}
