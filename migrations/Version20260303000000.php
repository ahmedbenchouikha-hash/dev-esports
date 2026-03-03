<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Empty migration - createdAt already exists on player table';
    }

    public function up(Schema $schema): void
    {
        // Player entity already has created_at column
        // This migration is a placeholder for now
    }

    public function down(Schema $schema): void
    {
    }
}
