<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211232000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reclamation, admin_response, punition, and notification tables';
    }

    public function up(Schema $schema): void
    {
<<<<<<< HEAD
        if ($this->connection->createSchemaManager()->tablesExist(['reclamation'])) {
            return;
        }

=======
>>>>>>> module-rewards
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, etat VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', attachment_filename VARCHAR(255) DEFAULT NULL, INDEX IDX_3B3A2C0999E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_response (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE KEY UNIQ_52A85A0CF826B5F (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE punition (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', player_status VARCHAR(20) NOT NULL, UNIQUE KEY UNIQ_3A26754CF826B5F (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CAF826B5F (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_3B3A2C0999E6F5DF FOREIGN KEY (player_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE admin_response ADD CONSTRAINT FK_52A85A0CF826B5F FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE punition ADD CONSTRAINT FK_3A26754CF826B5F FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF826B5F FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF826B5F');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_3B3A2C0999E6F5DF');
        $this->addSql('ALTER TABLE admin_response DROP FOREIGN KEY FK_52A85A0CF826B5F');
        $this->addSql('ALTER TABLE punition DROP FOREIGN KEY FK_3A26754CF826B5F');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE admin_response');
        $this->addSql('DROP TABLE punition');
        $this->addSql('DROP TABLE notification');
    }
}
