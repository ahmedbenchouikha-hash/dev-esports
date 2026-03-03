<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type column to team_invitation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE team_invitation ADD COLUMN IF NOT EXISTS type VARCHAR(20) NOT NULL DEFAULT 'invitation'");
        $this->addSql("UPDATE team_invitation SET type = 'invitation' WHERE type IS NULL OR type = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_invitation DROP COLUMN IF EXISTS type');
    }
}
