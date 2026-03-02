<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add facture column to depense table for receipt/invoice file upload
 */
final class Version20260228000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add facture column to depense table for file upload storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE depense ADD COLUMN facture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE depense DROP COLUMN facture');
    }
}
