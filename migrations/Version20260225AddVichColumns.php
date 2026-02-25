<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225AddVichColumns extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Vich Uploader fields for Team, Budget, and Depense';
    }

    public function up(Schema $schema): void
    {
        // Team: Add logo_file column
        $this->addSql('ALTER TABLE team ADD logo_file VARCHAR(255) NULL');

        // Budget: Add justificatif columns
        $this->addSql('ALTER TABLE budget ADD justificatif VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE budget ADD justificatif_file VARCHAR(255) NULL');

        // Depense: Add facture columns
        $this->addSql('ALTER TABLE depense ADD facture VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE depense ADD facture_file VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        // Team
        $this->addSql('ALTER TABLE team DROP COLUMN logo_file');

        // Budget
        $this->addSql('ALTER TABLE budget DROP COLUMN justificatif');
        $this->addSql('ALTER TABLE budget DROP COLUMN justificatif_file');

        // Depense
        $this->addSql('ALTER TABLE depense DROP COLUMN facture');
        $this->addSql('ALTER TABLE depense DROP COLUMN facture_file');
    }
}
