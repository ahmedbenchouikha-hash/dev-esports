<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Player-Team ManyToMany relationship - This migration was already applied to database
 */
final class Version20260221210544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert Player-Team relationship from OneToMany to ManyToMany (Already applied)';
    }

    public function up(Schema $schema): void
    {
        // Migration already applied - player_team table exists with data
        // This migration is a no-op as the changes were already made manually
    }

    public function down(Schema $schema): void
    {
        // This migration should not be reverted
    }
}
