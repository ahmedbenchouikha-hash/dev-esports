<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new fields to Recompense and DemandeRecompense entities for AI analysis and Giphy integration';
    }

    public function up(Schema $schema): void
    {
        // Set tournament_id for existing recompense records where it's NULL or 0
        $this->addSql('UPDATE recompense SET tournament_id = 1 WHERE tournament_id IS NULL OR tournament_id = 0');

        // Add foreign key constraint for tournament_id to recompense (column already exists)
        $this->addSql('ALTER TABLE recompense ADD CONSTRAINT FK_6D7BF9C633D666AB FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6D7BF9C633D666AB ON recompense (tournament_id)');

        // Add all columns to demande_recompense table
        $this->addSql('ALTER TABLE demande_recompense ADD created_by_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD verification_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD email_verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD is_prioritaire TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_legitimacy_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_fraud_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_confidence_level DOUBLE DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_key_points JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_sentiment VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_suggested_reward_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_analysis_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_should_auto_approve TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD ai_analyzed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key and index from recompense table
        $this->addSql('ALTER TABLE recompense DROP FOREIGN KEY FK_6D7BF9C633D666AB');
        $this->addSql('DROP INDEX IDX_6D7BF9C633D666AB ON recompense');

        // Drop columns from demande_recompense table
        $this->addSql('ALTER TABLE demande_recompense DROP created_by_email, DROP verification_token, DROP email_verified_at, DROP is_prioritaire, DROP ai_legitimacy_score, DROP ai_fraud_type, DROP ai_confidence_level, DROP ai_key_points, DROP ai_sentiment, DROP ai_suggested_reward_type, DROP ai_analysis_reason, DROP ai_should_auto_approve, DROP ai_analyzed_at');
    }
}
