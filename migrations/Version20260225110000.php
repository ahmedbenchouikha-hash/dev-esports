<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create team chat message table for real-time team chat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE team_chat_message (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, sender_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)", INDEX IDX_2E06AFCB296CD8AE (team_id), INDEX IDX_2E06AFCBF624B39D (sender_id), INDEX IDX_2E06AFCB75E3E3D4 (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_chat_message ADD CONSTRAINT FK_2E06AFCB296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_chat_message ADD CONSTRAINT FK_2E06AFCBF624B39D FOREIGN KEY (sender_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE team_chat_message');
    }
}
