<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228222735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, etablissement_nom VARCHAR(255) NOT NULL, etablissement_code_postal VARCHAR(10) NOT NULL, etablissement_ville VARCHAR(50) NOT NULL, contact_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_mobile VARCHAR(20) NOT NULL, role VARCHAR(255) DEFAULT NULL, state INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etablissement ADD origin_inscription_id INT DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592C858865E9 FOREIGN KEY (origin_inscription_id) REFERENCES inscription (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20FD592C858865E9 ON etablissement (origin_inscription_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592C858865E9');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP INDEX UNIQ_20FD592C858865E9 ON etablissement');
        $this->addSql('ALTER TABLE etablissement DROP origin_inscription_id, DROP email');
    }
}
