<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add approval_status column to user table for player approval system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD approval_status VARCHAR(20) DEFAULT \'pending\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN approval_status');
    }
}
