<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create budget table for budget management';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->createSchemaManager()->tablesExist(['budget'])) {
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, montant_alloue DOUBLE PRECISION NOT NULL, montant_utilise DOUBLE PRECISION NOT NULL DEFAULT 0, date_allocation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, statut VARCHAR(50) NOT NULL DEFAULT "actif", INDEX IDX_73F2F77296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77296CD8AE');
        $this->addSql('DROP TABLE budget');
    }
}
