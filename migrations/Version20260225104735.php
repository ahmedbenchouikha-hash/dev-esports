<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225104735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add player_id foreign key to payment table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D99E6F5DF ON payment (player_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D99E6F5DF');
        $this->addSql('DROP INDEX IDX_6D28840D99E6F5DF ON payment');
        $this->addSql('ALTER TABLE payment DROP COLUMN player_id');
    }
}
