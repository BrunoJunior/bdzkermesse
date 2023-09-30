<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210161644 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activite CHANGE kermesse_id kermesse_id INT DEFAULT NULL, CHANGE date date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE benevole CHANGE portable portable VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_benevole ADD validee TINYINT(1) NOT NULL, CHANGE token token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE kermesse CHANGE theme theme VARCHAR(255) DEFAULT NULL, CHANGE montant_ticket montant_ticket INT DEFAULT NULL, CHANGE duree_creneau duree_creneau VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('ALTER TABLE membre CHANGE etablissement_id etablissement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette CHANGE nombre_ticket nombre_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement CHANGE mode mode INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket CHANGE membre_id membre_id INT DEFAULT NULL, CHANGE remboursement_id remboursement_id INT DEFAULT NULL, CHANGE duplicata duplicata VARCHAR(255) DEFAULT NULL, CHANGE commentaire commentaire VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activite CHANGE kermesse_id kermesse_id INT DEFAULT NULL, CHANGE date date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE benevole CHANGE portable portable VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE inscription_benevole DROP validee, CHANGE token token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE kermesse CHANGE theme theme VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE montant_ticket montant_ticket INT DEFAULT NULL, CHANGE duree_creneau duree_creneau VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('ALTER TABLE membre CHANGE etablissement_id etablissement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette CHANGE nombre_ticket nombre_ticket INT DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement CHANGE mode mode INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket CHANGE membre_id membre_id INT DEFAULT NULL, CHANGE remboursement_id remboursement_id INT DEFAULT NULL, CHANGE duplicata duplicata VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE commentaire commentaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
