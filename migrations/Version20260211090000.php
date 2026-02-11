<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create password_reset_token table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_PASSWORD_RESET_TOKEN_TOKEN (token), INDEX IDX_PASSWORD_RESET_TOKEN_USER_ID (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_PASSWORD_RESET_TOKEN_USER_ID FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_PASSWORD_RESET_TOKEN_USER_ID');
        $this->addSql('DROP TABLE password_reset_token');
    }
}
