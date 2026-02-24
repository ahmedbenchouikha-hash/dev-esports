<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create payment table for Stripe integration
 */
final class Version20260222130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment table for Stripe integration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE payment (
            id INT AUTO_INCREMENT NOT NULL,
            ticket_id INT NOT NULL,
            payment_intent_id VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT "pending",
            customer_email VARCHAR(255) NOT NULL,
            amount DOUBLE PRECISION NOT NULL,
            quantity_purchased INT NOT NULL DEFAULT 1,
            customer_name VARCHAR(255) NULL,
            customer_phone VARCHAR(255) NULL,
            notes LONGTEXT NULL,
            refunded_at DATETIME NULL,
            refund_amount DOUBLE PRECISION NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY UNIQ_6D28840D67C3B3A (payment_intent_id),
            KEY IDX_6D28840D712520F3 (ticket_id),
            FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE payment');
    }
}
