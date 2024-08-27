<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230917165048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pack (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, amount VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pack_countries (pack_id INT NOT NULL, countries_id INT NOT NULL, INDEX IDX_37F8441B1919B217 (pack_id), INDEX IDX_37F8441BAEBAE514 (countries_id), PRIMARY KEY(pack_id, countries_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, is_paid TINYINT(1) NOT NULL, validity DATETIME NOT NULL, stripe_session_id VARCHAR(255) NOT NULL, paypal_order_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pack_countries ADD CONSTRAINT FK_37F8441B1919B217 FOREIGN KEY (pack_id) REFERENCES pack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pack_countries ADD CONSTRAINT FK_37F8441BAEBAE514 FOREIGN KEY (countries_id) REFERENCES countries (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE orders_details');
        $this->addSql('ALTER TABLE orders ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE products ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5AA76ED395 ON products (user_id)');
        $this->addSql('ALTER TABLE users ADD activation_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orders_details (id INT AUTO_INCREMENT NOT NULL, total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE pack_countries DROP FOREIGN KEY FK_37F8441B1919B217');
        $this->addSql('ALTER TABLE pack_countries DROP FOREIGN KEY FK_37F8441BAEBAE514');
        $this->addSql('DROP TABLE countries');
        $this->addSql('DROP TABLE pack');
        $this->addSql('DROP TABLE pack_countries');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('ALTER TABLE orders DROP status');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AA76ED395');
        $this->addSql('DROP INDEX IDX_B3BA5A5AA76ED395 ON products');
        $this->addSql('ALTER TABLE products DROP user_id');
        $this->addSql('ALTER TABLE users DROP activation_code');
    }
}
