<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230605211209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_activite (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_758A72E9FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_activite ADD CONSTRAINT FK_758A72E9FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE activite ADD type_id INT DEFAULT NULL, CHANGE only_for_planning only_for_planning TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515C54C8C93 FOREIGN KEY (type_id) REFERENCES type_activite (id)');
        $this->addSql('CREATE INDEX IDX_B8755515C54C8C93 ON activite (type_id)');
        $this->addSql("INSERT INTO type_activite(id, nom) VALUES (-1, 'Autre (précisez)') ");
        $this->addSql("INSERT INTO type_activite(nom) VALUES 
                              ('Jeu à lot immédiat'),
                              ('Jeu à meilleur score'),
                              ('Tirage au sort'),
                              ('Vente directe'),
                              ('Animation'),
                              ('Surveillance')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515C54C8C93');
        $this->addSql('ALTER TABLE type_activite DROP FOREIGN KEY FK_758A72E9FF631228');
        $this->addSql('DROP TABLE type_activite');
        $this->addSql('DROP INDEX IDX_B8755515C54C8C93 ON activite');
        $this->addSql('ALTER TABLE activite DROP type_id, CHANGE only_for_planning only_for_planning TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
