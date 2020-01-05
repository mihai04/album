<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200105044832 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', full_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `album` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, isrc VARCHAR(12) NOT NULL, image VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_39986E43F86F9A0A (isrc), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `review` (id INT AUTO_INCREMENT NOT NULL, reviewer INT NOT NULL, album INT DEFAULT NULL, title VARCHAR(255) NOT NULL, review LONGTEXT NOT NULL, rating VARCHAR(255) NOT NULL, timestamp VARCHAR(255) NOT NULL, INDEX IDX_794381C6E0472730 (reviewer), INDEX IDX_794381C639986E43 (album), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `review` ADD CONSTRAINT FK_794381C6E0472730 FOREIGN KEY (reviewer) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `review` ADD CONSTRAINT FK_794381C639986E43 FOREIGN KEY (album) REFERENCES `album` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `review` DROP FOREIGN KEY FK_794381C6E0472730');
        $this->addSql('ALTER TABLE `review` DROP FOREIGN KEY FK_794381C639986E43');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE `album`');
        $this->addSql('DROP TABLE `review`');
    }
}
