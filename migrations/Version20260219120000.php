<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create match_statistic table for Match Statistics entity';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->createSchemaManager()->tablesExist(['match_statistic'])) {
            return;
        }

        $this->addSql('CREATE TABLE match_statistic (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, game_id INT NOT NULL, kills INT NOT NULL, deaths INT NOT NULL, assists INT NOT NULL, damage_dealt DOUBLE PRECISION NOT NULL, damage_taken DOUBLE PRECISION NOT NULL, objectives_destroyed INT NOT NULL, gold_earned DOUBLE PRECISION NOT NULL, role VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7D5AC69599E6F5DF (player_id), INDEX IDX_7D5AC695E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE match_statistic ADD CONSTRAINT FK_7D5AC69599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE match_statistic ADD CONSTRAINT FK_7D5AC695E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE match_statistic DROP FOREIGN KEY FK_7D5AC69599E6F5DF');
        $this->addSql('ALTER TABLE match_statistic DROP FOREIGN KEY FK_7D5AC695E48FD905');
        $this->addSql('DROP TABLE match_statistic');
    }
}
