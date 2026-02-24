<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260221200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create team_invitation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE team_invitation (
            id INT AUTO_INCREMENT NOT NULL,
            team_id INT NOT NULL,
            player_id INT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT "pending",
            created_at DATETIME NOT NULL,
            responded_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY(team_id) REFERENCES team(id),
            FOREIGN KEY(player_id) REFERENCES player(id),
            UNIQUE KEY unique_team_player (team_id, player_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE team_invitation');
    }
}
