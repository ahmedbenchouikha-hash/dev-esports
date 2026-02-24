<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove captain_id and membres columns from team table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team DROP COLUMN IF EXISTS captain_id');
        $this->addSql('ALTER TABLE team DROP COLUMN IF EXISTS membres');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team ADD COLUMN captain_id INT NULL');
        $this->addSql('ALTER TABLE team ADD COLUMN membres JSON NULL');
    }
}
