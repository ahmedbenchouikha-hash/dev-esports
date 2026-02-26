<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create player_team join table for many-to-many relationship';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS player_team (
            player_id INT NOT NULL,
            team_id INT NOT NULL,
            PRIMARY KEY (player_id, team_id),
            INDEX IDX_8B8A84C699E6F5DF (player_id),
            INDEX IDX_8B8A84C6296CD8AE (team_id),
            CONSTRAINT FK_8B8A84C699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE,
            CONSTRAINT FK_8B8A84C6296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS player_team');
    }
}
