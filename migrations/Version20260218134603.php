<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218134603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS admin_response (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, reclamation_id INT NOT NULL, UNIQUE INDEX UNIQ_AADB0CFD2D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS budget (id INT AUTO_INCREMENT NOT NULL, montant_alloue DOUBLE PRECISION NOT NULL, montant_utilise DOUBLE PRECISION NOT NULL, date_allocation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, statut VARCHAR(50) NOT NULL, team_id INT NOT NULL, INDEX IDX_73F2F77B296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS demande_recompense (id INT AUTO_INCREMENT NOT NULL, nom_demandeur VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, motif LONGTEXT DEFAULT NULL, date_demande DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, recompense_id INT NOT NULL, INDEX IDX_E857CA7F4D714096 (recompense_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS depense (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, montant DOUBLE PRECISION NOT NULL, description VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, categorie VARCHAR(255) DEFAULT NULL, team_id INT DEFAULT NULL, INDEX IDX_34059757296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, statut VARCHAR(50) NOT NULL, membres JSON DEFAULT NULL, captain_id INT DEFAULT NULL, couleur_equipe VARCHAR(50) DEFAULT NULL, jeu VARCHAR(100) DEFAULT NULL, niveau VARCHAR(50) DEFAULT NULL, date_validation DATETIME DEFAULT NULL, score INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS game (id INT AUTO_INCREMENT NOT NULL, score1 INT NOT NULL, score2 INT NOT NULL, matchdate DATETIME NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, team1_id INT NOT NULL, team2_id INT NOT NULL, tournament_id INT DEFAULT NULL, INDEX IDX_232B318CE72BCFA4 (team1_id), INDEX IDX_232B318CF59E604A (team2_id), INDEX IDX_232B318C33D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, reclamation_id INT DEFAULT NULL, INDEX IDX_BF5476CA2D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS password_reset_token (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_6B7BA4B65F37A13B (token), INDEX IDX_6B7BA4B6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS player (nickname VARCHAR(255) NOT NULL, role VARCHAR(255) DEFAULT NULL, player_status VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, team_id INT NOT NULL, id INT NOT NULL, INDEX IDX_98197A65296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS punition (id INT AUTO_INCREMENT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, player_status VARCHAR(20) NOT NULL, reclamation_id INT NOT NULL, UNIQUE INDEX UNIQ_3ACA36302D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS reclamation (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, attachment_filename VARCHAR(255) DEFAULT NULL, player_id INT DEFAULT NULL, INDEX IDX_CE60640499E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS recompense (id INT AUTO_INCREMENT NOT NULL, recompense VARCHAR(30) NOT NULL, type VARCHAR(50) NOT NULL, classement INT NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, detailed_description LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, jeu VARCHAR(100) DEFAULT NULL, niveau VARCHAR(50) DEFAULT NULL, couleur_equipe VARCHAR(50) DEFAULT NULL, membres JSON DEFAULT NULL, captain_id INT DEFAULT NULL, statut VARCHAR(50) NOT NULL, date_validation DATETIME DEFAULT NULL, score INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS tournament (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, status VARCHAR(50) NOT NULL, location VARCHAR(255) NOT NULL, prize_pool DOUBLE PRECISION DEFAULT NULL, rules JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, typeuser VARCHAR(50) DEFAULT NULL, approval_status VARCHAR(20) DEFAULT \'pending\' NOT NULL, confirmation_file VARCHAR(255) DEFAULT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, birth_date DATE DEFAULT NULL, discr VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS user_profile (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, address LONGTEXT DEFAULT NULL, birth_date DATE DEFAULT NULL, profile_picture VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE admin_response ADD CONSTRAINT FK_AADB0CFD2D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE demande_recompense ADD CONSTRAINT FK_E857CA7F4D714096 FOREIGN KEY (recompense_id) REFERENCES recompense (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CE72BCFA4 FOREIGN KEY (team1_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CF59E604A FOREIGN KEY (team2_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65BF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE punition ADD CONSTRAINT FK_3ACA36302D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_response DROP FOREIGN KEY FK_AADB0CFD2D6BA2D9');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B296CD8AE');
        $this->addSql('ALTER TABLE demande_recompense DROP FOREIGN KEY FK_E857CA7F4D714096');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_34059757296CD8AE');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CE72BCFA4');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CF59E604A');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C33D1A3E7');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2D6BA2D9');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65296CD8AE');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65BF396750');
        $this->addSql('ALTER TABLE punition DROP FOREIGN KEY FK_3ACA36302D6BA2D9');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640499E6F5DF');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE admin_response');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE demande_recompense');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE punition');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE recompense');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
