<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231001224041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE membre_bureau (id INT AUTO_INCREMENT NOT NULL, membre_id INT NOT NULL, bureau_id INT NOT NULL, role ENUM(\'membre\', \'president\', \'vice_president\', \'tresorier\', \'tresorier_adjoint\', \'secretaire\', \'secretaire_adjoint\') NOT NULL COMMENT \'(DC2Type:enumrole)\', INDEX IDX_8C664B7D6A99F74A (membre_id), INDEX IDX_8C664B7D32516FE2 (bureau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membre_bureau ADD CONSTRAINT FK_8C664B7D6A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE membre_bureau ADD CONSTRAINT FK_8C664B7D32516FE2 FOREIGN KEY (bureau_id) REFERENCES bureau (id)');
        $this->addSql('ALTER TABLE bureau ADD CONSTRAINT FK_166FDEC4FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membre_bureau DROP FOREIGN KEY FK_8C664B7D6A99F74A');
        $this->addSql('ALTER TABLE membre_bureau DROP FOREIGN KEY FK_8C664B7D32516FE2');
        $this->addSql('DROP TABLE membre_bureau');
        $this->addSql('ALTER TABLE bureau DROP FOREIGN KEY FK_166FDEC4FF631228');
    }
}
