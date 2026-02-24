<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create budget_alert table for budget monitoring and alerts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE budget_alert (
            id INT AUTO_INCREMENT NOT NULL,
            budget_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            budget_percentage DOUBLE PRECISION NOT NULL,
            remaining_amount DOUBLE PRECISION NOT NULL,
            sent_at DATETIME NOT NULL,
            sent_to VARCHAR(255) NULL,
            manager_notification_sent_at DATETIME NULL,
            admin_notification_sent_at DATETIME NULL,
            INDEX IDX_36949D63 (budget_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE budget_alert ADD CONSTRAINT FK_36949D633 FOREIGN KEY (budget_id) REFERENCES budget (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE budget_alert DROP FOREIGN KEY FK_36949D633');
        $this->addSql('DROP TABLE budget_alert');
    }
}
