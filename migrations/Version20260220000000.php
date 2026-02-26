<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tournament relationship to recompense and enhance demande_recompense with verification fields';
    }

    public function up(Schema $schema): void
    {
        // Add tournament_id to recompense table
        $this->addSql('ALTER TABLE recompense ADD COLUMN tournament_id INT NOT NULL AFTER description');
        $this->addSql('ALTER TABLE recompense ADD CONSTRAINT FK_95C57F53_TOURNAMENT FOREIGN KEY (tournament_id) REFERENCES tournament(id)');
        $this->addSql('CREATE INDEX IDX_RECOMPENSE_TOURNAMENT ON recompense(tournament_id)');

        // Add verification fields to demande_recompense table
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN verification_token VARCHAR(255) NULL AFTER statut');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN email_verified_at DATETIME NULL AFTER verification_token');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN is_prioritaire BOOLEAN NOT NULL DEFAULT 0 AFTER email_verified_at');

        // Update validation constraints on demande_recompense.email
        $this->addSql('ALTER TABLE demande_recompense MODIFY email VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove verification fields from demande_recompense
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN verification_token');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN email_verified_at');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN is_prioritaire');

        // Remove tournament constraint from recompense
        $this->addSql('ALTER TABLE recompense DROP FOREIGN KEY FK_95C57F53_TOURNAMENT');
        $this->addSql('DROP INDEX IDX_RECOMPENSE_TOURNAMENT ON recompense');
        $this->addSql('ALTER TABLE recompense DROP COLUMN tournament_id');
    }
}
