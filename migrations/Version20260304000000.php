<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tournament_registration table for tournament registrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS tournament_registration (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, team_id INT NOT NULL, player_id INT NOT NULL, team_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, status VARCHAR(50) DEFAULT \'pending\' NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_33D666AB33D666AB (tournament_id), INDEX IDX_33D666ABAD6D7735 (team_id), INDEX IDX_33D666AB99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament_registration ADD CONSTRAINT FK_33D666AB33D666AB FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_registration ADD CONSTRAINT FK_33D666ABAD6D7735 FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_registration ADD CONSTRAINT FK_33D666AB99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tournament_registration DROP FOREIGN KEY FK_33D666AB33D666AB');
        $this->addSql('ALTER TABLE tournament_registration DROP FOREIGN KEY FK_33D666ABAD6D7735');
        $this->addSql('ALTER TABLE tournament_registration DROP FOREIGN KEY FK_33D666AB99E6F5DF');
        $this->addSql('DROP TABLE tournament_registration');
    }
}
