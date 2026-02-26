<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Giphy integration to Recompense entity for professional reward illustrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recompense ADD giphy_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recompense ADD motif_suggere_ia LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE recompense ADD giphy_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recompense DROP COLUMN giphy_url');
        $this->addSql('ALTER TABLE recompense DROP COLUMN motif_suggere_ia');
        $this->addSql('ALTER TABLE recompense DROP COLUMN giphy_updated_at');
    }
}
