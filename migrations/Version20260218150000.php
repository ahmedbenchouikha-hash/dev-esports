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
                team_id INT NOT NULL,
                player_id INT NOT NULL,
                status VARCHAR(50) NOT NULL DEFAULT \'pending\',
                motivation LONGTEXT NOT NULL,
                experience_level VARCHAR(50) NOT NULL,
                previous_achievements LONGTEXT DEFAULT NULL,
                admin_notes LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                reviewed_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY tournament_team_unique (tournament_id, team_id),
                KEY idx_tournament_status (tournament_id, status),
                KEY idx_team_id (team_id),
                KEY idx_player_id (player_id),
                CONSTRAINT FK_tournament FOREIGN KEY (tournament_id) 
                    REFERENCES tournament (id) ON DELETE CASCADE,
                CONSTRAINT FK_team FOREIGN KEY (team_id) 
                    REFERENCES team (id) ON DELETE CASCADE,
                CONSTRAINT FK_player FOREIGN KEY (player_id) 
                    REFERENCES user (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tournament_registration');
    }
}
