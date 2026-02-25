<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase punition.player_status length to support append-only multiple bans';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE punition MODIFY player_status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE punition MODIFY player_status VARCHAR(20) NOT NULL');
    }
}
