<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make typeuser column nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY typeuser VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY typeuser VARCHAR(50) NOT NULL');
    }
}
