<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230609215030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT NOT NULL, directory_id INT DEFAULT NULL, linked_to_id INT DEFAULT NULL, activite_id INT DEFAULT NULL, kermesse_id INT DEFAULT NULL, membre_id INT DEFAULT NULL, recette_id INT DEFAULT NULL, remboursement_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, type INT NOT NULL, datec DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D8698A76FF631228 (etablissement_id), INDEX IDX_D8698A762C94069F (directory_id), INDEX IDX_D8698A768031A592 (linked_to_id), INDEX IDX_D8698A769B0F88B1 (activite_id), INDEX IDX_D8698A76C5B5ACBC (kermesse_id), INDEX IDX_D8698A766A99F74A (membre_id), INDEX IDX_D8698A7689312FE9 (recette_id), INDEX IDX_D8698A76F61EE8B (remboursement_id), INDEX IDX_D8698A76700047D2 (ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A762C94069F FOREIGN KEY (directory_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768031A592 FOREIGN KEY (linked_to_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A769B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7689312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76F61EE8B FOREIGN KEY (remboursement_id) REFERENCES remboursement (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76FF631228');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A762C94069F');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A768031A592');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A769B0F88B1');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76C5B5ACBC');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766A99F74A');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7689312FE9');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76F61EE8B');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76700047D2');
        $this->addSql('DROP TABLE document');
    }
}
