<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove old payment fields from ticket table (now in separate payment table)
 */
final class Version20260222135000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove payment-related columns from ticket table (now in payment table)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket DROP COLUMN payment_intent_id');
        $this->addSql('ALTER TABLE ticket DROP COLUMN payment_status');
        $this->addSql('ALTER TABLE ticket DROP COLUMN customer_email');
        $this->addSql('ALTER TABLE ticket DROP COLUMN base_price');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket ADD COLUMN payment_intent_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD COLUMN payment_status VARCHAR(50) DEFAULT "pending"');
        $this->addSql('ALTER TABLE ticket ADD COLUMN customer_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD COLUMN base_price DOUBLE DEFAULT NULL');
    }
}
