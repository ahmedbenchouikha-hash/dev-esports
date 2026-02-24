<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove score column from match_statistic table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE match_statistic DROP COLUMN IF EXISTS score');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE match_statistic ADD COLUMN score INT DEFAULT NULL COMMENT "Player score based on match result"');
    }
}
