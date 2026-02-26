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
        // Skipped - base tables already created by Version20260211182607
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
