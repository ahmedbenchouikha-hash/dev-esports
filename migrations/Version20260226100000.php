<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Change qr_code column to LONGTEXT
 */
final class Version20260226100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change qr_code column from VARCHAR to LONGTEXT to store full base64 QR code images';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket MODIFY qr_code LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket MODIFY qr_code VARCHAR(255) DEFAULT NULL');
    }
}
