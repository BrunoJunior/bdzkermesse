<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180819135838 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activite ADD etablissement_id INT');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_B8755515FF631228 ON activite (etablissement_id)');
        $this->addSql('ALTER TABLE depense ADD etablissement_id INT');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_34059757FF631228 ON depense (etablissement_id)');
        $this->addSql('ALTER TABLE recette ADD etablissement_id INT, CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB6390FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_49BB6390FF631228 ON recette (etablissement_id)');
        $this->addSql('ALTER TABLE remboursement ADD etablissement_id INT');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EFFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_C0C0D9EFFF631228 ON remboursement (etablissement_id)');
        $this->addSql('ALTER TABLE ticket ADD etablissement_id INT');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3FF631228 ON ticket (etablissement_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515FF631228');
        $this->addSql('DROP INDEX IDX_B8755515FF631228 ON activite');
        $this->addSql('ALTER TABLE activite DROP etablissement_id');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_34059757FF631228');
        $this->addSql('DROP INDEX IDX_34059757FF631228 ON depense');
        $this->addSql('ALTER TABLE depense DROP etablissement_id');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB6390FF631228');
        $this->addSql('DROP INDEX IDX_49BB6390FF631228 ON recette');
        $this->addSql('ALTER TABLE recette DROP etablissement_id, CHANGE date date DATE DEFAULT \'1970-01-01\' NOT NULL');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EFFF631228');
        $this->addSql('DROP INDEX IDX_C0C0D9EFFF631228 ON remboursement');
        $this->addSql('ALTER TABLE remboursement DROP etablissement_id');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FF631228');
        $this->addSql('DROP INDEX IDX_97A0ADA3FF631228 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP etablissement_id');
    }
}
