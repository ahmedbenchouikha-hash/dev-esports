<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new columns to teams table for esports features';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['team'])) {
            return;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns('team');
        if (isset($columns['logo'])) {
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team ADD logo VARCHAR(255) DEFAULT NULL, ADD jeu VARCHAR(100) DEFAULT NULL, ADD niveau VARCHAR(50) DEFAULT NULL, ADD couleur_equipe VARCHAR(50) DEFAULT NULL, ADD membres JSON DEFAULT NULL, ADD captain_id INT DEFAULT NULL, ADD statut VARCHAR(50) DEFAULT \'en attente\', ADD date_validation DATETIME DEFAULT NULL, ADD score INT DEFAULT 0, ADD detailed_description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP logo, DROP jeu, DROP niveau, DROP couleur_equipe, DROP membres, DROP captain_id, DROP statut, DROP date_validation, DROP score, DROP detailed_description');
    }
}
