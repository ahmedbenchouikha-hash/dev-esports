<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rules JSON column to tournament table';
    }

    public function up(Schema $schema): void
    {
        // Skip - rules column already added in Version20260211182607
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tournament DROP rules');
    }
}
