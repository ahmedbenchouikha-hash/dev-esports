<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Move QR code generation from Ticket to Payment
 */
final class Version20260226110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move qr_code column from ticket table to payment table - QR codes are for payments/purchases, not ticket types';
    }

    public function up(Schema $schema): void
    {
        // Remove qr_code from ticket table
        $this->addSql('ALTER TABLE ticket DROP COLUMN qr_code');
        
        // Add qr_code to payment table
        $this->addSql('ALTER TABLE payment ADD COLUMN qr_code LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Add qr_code back to ticket table
        $this->addSql('ALTER TABLE ticket ADD COLUMN qr_code LONGTEXT DEFAULT NULL');
        
        // Remove qr_code from payment table
        $this->addSql('ALTER TABLE payment DROP COLUMN qr_code');
    }
}
