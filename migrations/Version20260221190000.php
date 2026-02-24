<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make Player nickname and team optional
 */
final class Version20260221190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make Player nickname and team nullable to allow optional player profiles during registration';
    }

    public function up(Schema $schema): void
    {
        // Make nickname nullable
        $this->addSql('ALTER TABLE player MODIFY nickname VARCHAR(255) NULL');
        
        // Make team nullable
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('ALTER TABLE player MODIFY team_id INT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // Revert nickname to NOT NULL
        $this->addSql('ALTER TABLE player MODIFY nickname VARCHAR(255) NOT NULL');
        
        // Revert team to NOT NULL
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('ALTER TABLE player MODIFY team_id INT NOT NULL');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
