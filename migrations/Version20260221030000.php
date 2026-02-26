<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les colonnes IA à DemandeRecompense
 */
final class Version20260221030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter les colonnes IA à la table demande_recompense';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_legitimacy_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_fraud_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_confidence_level DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_key_points JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_sentiment VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_suggested_reward_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_analysis_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_should_auto_approve TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_recompense ADD COLUMN ai_analyzed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_legitimacy_score');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_fraud_type');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_confidence_level');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_key_points');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_sentiment');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_suggested_reward_type');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_analysis_reason');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_should_auto_approve');
        $this->addSql('ALTER TABLE demande_recompense DROP COLUMN ai_analyzed_at');
    }
}
