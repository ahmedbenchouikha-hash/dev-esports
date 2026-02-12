<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add playerStatus column to player table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE player ADD player_status VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE player DROP COLUMN player_status');
    }
}
