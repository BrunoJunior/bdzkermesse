<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180821185926 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kermesse (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT NOT NULL, annee INT NOT NULL, theme VARCHAR(255) DEFAULT NULL, montant_ticket INT DEFAULT NULL, INDEX IDX_75E0712AFF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kermesse_membre (kermesse_id INT NOT NULL, membre_id INT NOT NULL, INDEX IDX_D001336DC5B5ACBC (kermesse_id), INDEX IDX_D001336D6A99F74A (membre_id), PRIMARY KEY(kermesse_id, membre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, kermesse_id INT DEFAULT NULL, etablissement_id INT NOT NULL, nom VARCHAR(255) NOT NULL, accepte_tickets TINYINT(1) NOT NULL, accepte_monnaie TINYINT(1) NOT NULL, caisse_centrale TINYINT(1) NOT NULL, INDEX IDX_B8755515C5B5ACBC (kermesse_id), INDEX IDX_B8755515FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membre (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, defaut TINYINT(1) NOT NULL, INDEX IDX_F6B4FB29FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recette (id INT AUTO_INCREMENT NOT NULL, activite_id INT NOT NULL, etablissement_id INT NOT NULL, montant INT NOT NULL, nombre_ticket INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, date DATE NOT NULL, report_stock TINYINT(1) NOT NULL, INDEX IDX_49BB63909B0F88B1 (activite_id), INDEX IDX_49BB6390FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, kermesse_id INT NOT NULL, membre_id INT DEFAULT NULL, remboursement_id INT DEFAULT NULL, etablissement_id INT NOT NULL, numero VARCHAR(255) NOT NULL, montant INT NOT NULL, fournisseur VARCHAR(255) NOT NULL, date DATE NOT NULL, duplicata VARCHAR(255) DEFAULT NULL, INDEX IDX_97A0ADA3C5B5ACBC (kermesse_id), INDEX IDX_97A0ADA36A99F74A (membre_id), INDEX IDX_97A0ADA3F61EE8B (remboursement_id), INDEX IDX_97A0ADA3FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, activite_id INT NOT NULL, etablissement_id INT NOT NULL, montant INT NOT NULL, INDEX IDX_34059757700047D2 (ticket_id), INDEX IDX_340597579B0F88B1 (activite_id), INDEX IDX_34059757FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(32) NOT NULL, nom VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, admin TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_20FD592CF85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE remboursement (id INT AUTO_INCREMENT NOT NULL, membre_id INT NOT NULL, etablissement_id INT NOT NULL, date DATE NOT NULL, montant INT NOT NULL, mode INT NOT NULL, numero_suivi VARCHAR(255) NOT NULL, INDEX IDX_C0C0D9EF6A99F74A (membre_id), INDEX IDX_C0C0D9EFFF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kermesse ADD CONSTRAINT FK_75E0712AFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE kermesse_membre ADD CONSTRAINT FK_D001336DC5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kermesse_membre ADD CONSTRAINT FK_D001336D6A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515C5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE membre ADD CONSTRAINT FK_F6B4FB29FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB63909B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB6390FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3C5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA36A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3F61EE8B FOREIGN KEY (remboursement_id) REFERENCES remboursement (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_340597579B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF6A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EFFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kermesse_membre DROP FOREIGN KEY FK_D001336DC5B5ACBC');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515C5B5ACBC');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3C5B5ACBC');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB63909B0F88B1');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_340597579B0F88B1');
        $this->addSql('ALTER TABLE kermesse_membre DROP FOREIGN KEY FK_D001336D6A99F74A');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA36A99F74A');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF6A99F74A');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_34059757700047D2');
        $this->addSql('ALTER TABLE kermesse DROP FOREIGN KEY FK_75E0712AFF631228');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515FF631228');
        $this->addSql('ALTER TABLE membre DROP FOREIGN KEY FK_F6B4FB29FF631228');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB6390FF631228');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FF631228');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_34059757FF631228');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EFFF631228');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3F61EE8B');
        $this->addSql('DROP TABLE kermesse');
        $this->addSql('DROP TABLE kermesse_membre');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE membre');
        $this->addSql('DROP TABLE recette');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE remboursement');
    }
}
