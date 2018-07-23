<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180722170700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, kermesse_id_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_B875551513BC7667 (kermesse_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, ticket_id_id INT NOT NULL, activite_id_id INT NOT NULL, montant INT NOT NULL, INDEX IDX_340597575774FDDC (ticket_id_id), INDEX IDX_34059757C1385E5E (activite_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) NOT NULL, nom VARCHAR(255) NOT NULL, motdepasse VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kermesse (id INT AUTO_INCREMENT NOT NULL, etablissement_id_id INT NOT NULL, annee INT NOT NULL, theme VARCHAR(255) DEFAULT NULL, INDEX IDX_75E0712AFC5092A6 (etablissement_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membre (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membre_kermesse (membre_id INT NOT NULL, kermesse_id INT NOT NULL, INDEX IDX_C08299196A99F74A (membre_id), INDEX IDX_C0829919C5B5ACBC (kermesse_id), PRIMARY KEY(membre_id, kermesse_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recette (id INT AUTO_INCREMENT NOT NULL, activite_id_id INT NOT NULL, montant INT NOT NULL, nombre_ticket INT DEFAULT NULL, montant_ticket INT DEFAULT NULL, INDEX IDX_49BB6390C1385E5E (activite_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE remboursement (id INT AUTO_INCREMENT NOT NULL, membre_id_id INT NOT NULL, date DATE NOT NULL, montant INT NOT NULL, mode INT NOT NULL, numero_suivi VARCHAR(255) NOT NULL, INDEX IDX_C0C0D9EFC96291D6 (membre_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, kermesse_id_id INT NOT NULL, membre_id_id INT DEFAULT NULL, remboursement_id_id INT DEFAULT NULL, numero VARCHAR(255) NOT NULL, montant INT NOT NULL, fournisseur VARCHAR(255) NOT NULL, date DATE NOT NULL, INDEX IDX_97A0ADA313BC7667 (kermesse_id_id), INDEX IDX_97A0ADA3C96291D6 (membre_id_id), INDEX IDX_97A0ADA37169BEF2 (remboursement_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B875551513BC7667 FOREIGN KEY (kermesse_id_id) REFERENCES kermesse (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_340597575774FDDC FOREIGN KEY (ticket_id_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757C1385E5E FOREIGN KEY (activite_id_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE kermesse ADD CONSTRAINT FK_75E0712AFC5092A6 FOREIGN KEY (etablissement_id_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE membre_kermesse ADD CONSTRAINT FK_C08299196A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membre_kermesse ADD CONSTRAINT FK_C0829919C5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB6390C1385E5E FOREIGN KEY (activite_id_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EFC96291D6 FOREIGN KEY (membre_id_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA313BC7667 FOREIGN KEY (kermesse_id_id) REFERENCES kermesse (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3C96291D6 FOREIGN KEY (membre_id_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA37169BEF2 FOREIGN KEY (remboursement_id_id) REFERENCES remboursement (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_34059757C1385E5E');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB6390C1385E5E');
        $this->addSql('ALTER TABLE kermesse DROP FOREIGN KEY FK_75E0712AFC5092A6');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B875551513BC7667');
        $this->addSql('ALTER TABLE membre_kermesse DROP FOREIGN KEY FK_C0829919C5B5ACBC');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA313BC7667');
        $this->addSql('ALTER TABLE membre_kermesse DROP FOREIGN KEY FK_C08299196A99F74A');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EFC96291D6');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3C96291D6');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA37169BEF2');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_340597575774FDDC');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE kermesse');
        $this->addSql('DROP TABLE membre');
        $this->addSql('DROP TABLE membre_kermesse');
        $this->addSql('DROP TABLE recette');
        $this->addSql('DROP TABLE remboursement');
        $this->addSql('DROP TABLE ticket');
    }
}
