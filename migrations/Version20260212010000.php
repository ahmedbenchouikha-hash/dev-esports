<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add firstName, lastName, and birthDate columns to user table';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['user'])) {
            return;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns('user');
        if (isset($columns['first_name'])) {
            return;
        }

        $this->addSql('ALTER TABLE `user` ADD first_name VARCHAR(100), ADD last_name VARCHAR(100), ADD birth_date DATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN first_name, DROP COLUMN last_name, DROP COLUMN birth_date');
    }
}
