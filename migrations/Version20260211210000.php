<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create depense table for expense management';
    }

    public function up(Schema $schema): void
    {
<<<<<<< HEAD
        if ($this->connection->createSchemaManager()->tablesExist(['depense'])) {
            return;
        }

=======
>>>>>>> module-rewards
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, montant DOUBLE PRECISION NOT NULL, description VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, statut VARCHAR(50) NOT NULL DEFAULT "en_attente", categorie VARCHAR(255) DEFAULT NULL, INDEX IDX_D0D4B11F296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_D0D4B11F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_D0D4B11F296CD8AE');
        $this->addSql('DROP TABLE depense');
    }
}
