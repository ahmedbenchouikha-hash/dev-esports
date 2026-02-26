<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ticket table for match module';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->createSchemaManager()->tablesExist(['ticket'])) {
            return;
        }

        $this->addSql('CREATE TABLE ticket (
            id INT AUTO_INCREMENT NOT NULL,
            game_id INT NOT NULL,
            ticket_number VARCHAR(100) NOT NULL UNIQUE,
            type VARCHAR(50) NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            quantity INT NOT NULL,
            sold INT NOT NULL DEFAULT 0,
            status VARCHAR(50) NOT NULL DEFAULT "available",
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (game_id) REFERENCES game(id),
            INDEX idx_game (game_id),
            INDEX idx_status (status),
            INDEX idx_type (type)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS ticket');
    }
}
