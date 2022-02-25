<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200209160420 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE benevole (id INT AUTO_INCREMENT NOT NULL, identite VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_valide TINYINT(1) NOT NULL, portable VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE creneau (id INT AUTO_INCREMENT NOT NULL, activite_id INT NOT NULL, debut TIME NOT NULL, fin TIME NOT NULL, nb_benevoles_recquis INT NOT NULL, INDEX IDX_F9668B5F9B0F88B1 (activite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription_benevole (id INT AUTO_INCREMENT NOT NULL, benevole_id INT NOT NULL, inscription_id INT NOT NULL, INDEX IDX_1D94307EE77B7C09 (benevole_id), INDEX IDX_1D94307E5DAC5993 (inscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE creneau ADD CONSTRAINT FK_F9668B5F9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE inscription_benevole ADD CONSTRAINT FK_1D94307EE77B7C09 FOREIGN KEY (benevole_id) REFERENCES benevole (id)');
        $this->addSql('ALTER TABLE inscription_benevole ADD CONSTRAINT FK_1D94307E5DAC5993 FOREIGN KEY (inscription_id) REFERENCES creneau (id)');
        $this->addSql('ALTER TABLE kermesse ADD duree_creneau VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', CHANGE theme theme VARCHAR(255) DEFAULT NULL, CHANGE montant_ticket montant_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD date DATE DEFAULT NULL, CHANGE kermesse_id kermesse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre CHANGE etablissement_id etablissement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette CHANGE nombre_ticket nombre_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement CHANGE mode mode INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket CHANGE membre_id membre_id INT DEFAULT NULL, CHANGE remboursement_id remboursement_id INT DEFAULT NULL, CHANGE duplicata duplicata VARCHAR(255) DEFAULT NULL, CHANGE commentaire commentaire VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inscription_benevole DROP FOREIGN KEY FK_1D94307EE77B7C09');
        $this->addSql('ALTER TABLE inscription_benevole DROP FOREIGN KEY FK_1D94307E5DAC5993');
        $this->addSql('DROP TABLE benevole');
        $this->addSql('DROP TABLE creneau');
        $this->addSql('DROP TABLE inscription_benevole');
        $this->addSql('ALTER TABLE activite DROP date, CHANGE kermesse_id kermesse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kermesse DROP duree_creneau, CHANGE theme theme VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\'\'NULL\'\'\' COLLATE `utf8mb4_unicode_ci`, CHANGE montant_ticket montant_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre CHANGE etablissement_id etablissement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette CHANGE nombre_ticket nombre_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement CHANGE mode mode INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket CHANGE membre_id membre_id INT DEFAULT NULL, CHANGE remboursement_id remboursement_id INT DEFAULT NULL, CHANGE duplicata duplicata VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\'\'NULL\'\'\' COLLATE `utf8mb4_unicode_ci`, CHANGE commentaire commentaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\'\'NULL\'\'\' COLLATE `utf8mb4_unicode_ci`');
    }
}
