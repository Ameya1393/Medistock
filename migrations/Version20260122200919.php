<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122200919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consumption (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, consumed_at DATETIME NOT NULL, logged_by VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, drug_id INT NOT NULL, INDEX IDX_2CFF2DF9AABCA765 (drug_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_2CFF2DF9AABCA765 FOREIGN KEY (drug_id) REFERENCES drug (id)');
        $this->addSql('ALTER TABLE drug ADD stock_quantity INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_2CFF2DF9AABCA765');
        $this->addSql('DROP TABLE consumption');
        $this->addSql('ALTER TABLE drug DROP stock_quantity');
    }
}
