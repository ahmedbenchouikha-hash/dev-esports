<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add payment fields to ticket table for Stripe integration
 */
final class Version20260222120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add payment fields to ticket table for Stripe integration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket ADD payment_intent_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD payment_status VARCHAR(50) DEFAULT \'pending\'');
        $this->addSql('ALTER TABLE ticket ADD customer_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD base_price DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket DROP payment_intent_id');
        $this->addSql('ALTER TABLE ticket DROP payment_status');
        $this->addSql('ALTER TABLE ticket DROP customer_email');
        $this->addSql('ALTER TABLE ticket DROP base_price');
    }
}
