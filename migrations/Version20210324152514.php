<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324152514 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE albums (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images_albums (images_id INT NOT NULL, albums_id INT NOT NULL, INDEX IDX_36188009D44F05E5 (images_id), INDEX IDX_36188009ECBB55AF (albums_id), PRIMARY KEY(images_id, albums_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE images_albums ADD CONSTRAINT FK_36188009D44F05E5 FOREIGN KEY (images_id) REFERENCES images (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE images_albums ADD CONSTRAINT FK_36188009ECBB55AF FOREIGN KEY (albums_id) REFERENCES albums (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE images_albums DROP FOREIGN KEY FK_36188009ECBB55AF');
        $this->addSql('ALTER TABLE images_albums DROP FOREIGN KEY FK_36188009D44F05E5');
        $this->addSql('DROP TABLE albums');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE images_albums');
    }
}
