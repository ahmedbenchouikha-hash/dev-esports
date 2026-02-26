<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_by_email column to demande_recompense table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demande_recompense ADD created_by_email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN created_by_email');
    }
}
