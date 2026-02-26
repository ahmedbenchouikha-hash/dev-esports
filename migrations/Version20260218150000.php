<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Tournament Registration System Migration
 * This migration adds support for players to join pending tournaments
 */
final class Version20260218150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tournament_registration table for tournament join system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE tournament_registration (
                id INT AUTO_INCREMENT NOT NULL,
                tournament_id INT NOT NULL,
                team_id INT,
                player_id INT,
                reviewed_by_id INT,
                status VARCHAR(50) NOT NULL DEFAULT \'pending\',
                team_name VARCHAR(255) NOT NULL,
                contact_email VARCHAR(255) NOT NULL,
                contact_phone VARCHAR(20),
                additional_info LONGTEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME,
                reviewed_at DATETIME,
                admin_notes LONGTEXT,
                PRIMARY KEY (id),
                KEY idx_tournament_status (tournament_id, status),
                KEY idx_team_id (team_id),
                KEY idx_player_id (player_id),
                KEY idx_reviewed_by_id (reviewed_by_id),
                CONSTRAINT FK_tournament FOREIGN KEY (tournament_id) 
                    REFERENCES tournament (id) ON DELETE CASCADE,
                CONSTRAINT FK_team FOREIGN KEY (team_id) 
                    REFERENCES team (id) ON DELETE SET NULL,
                CONSTRAINT FK_player FOREIGN KEY (player_id) 
                    REFERENCES user (id) ON DELETE SET NULL,
                CONSTRAINT FK_reviewed_by FOREIGN KEY (reviewed_by_id)
                    REFERENCES user (id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tournament_registration');
    }
}
