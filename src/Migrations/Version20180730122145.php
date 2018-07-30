<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180730122145 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kermesse_membre (kermesse_id INT NOT NULL, membre_id INT NOT NULL, INDEX IDX_D001336DC5B5ACBC (kermesse_id), INDEX IDX_D001336D6A99F74A (membre_id), PRIMARY KEY(kermesse_id, membre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kermesse_membre ADD CONSTRAINT FK_D001336DC5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kermesse_membre ADD CONSTRAINT FK_D001336D6A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE membre_kermesse');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE membre_kermesse (membre_id INT NOT NULL, kermesse_id INT NOT NULL, INDEX IDX_C08299196A99F74A (membre_id), INDEX IDX_C0829919C5B5ACBC (kermesse_id), PRIMARY KEY(membre_id, kermesse_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membre_kermesse ADD CONSTRAINT FK_C08299196A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membre_kermesse ADD CONSTRAINT FK_C0829919C5B5ACBC FOREIGN KEY (kermesse_id) REFERENCES kermesse (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE kermesse_membre');
    }
}
