<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120113104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user
                            (id INT AUTO_INCREMENT NOT NULL,
                            role_id INT DEFAULT NULL,
                            login VARCHAR(50) NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            phone VARCHAR(15) DEFAULT NULL,
                            first_name VARCHAR(35) NOT NULL,
                            last_name VARCHAR(35) NOT NULL,
                            patronymic VARCHAR(35) DEFAULT NULL,
                            INDEX IDX_8D93D649D60322AC (role_id),
                            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE user
                            ADD CONSTRAINT FK_8D93D649D60322AC
                            FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('DROP TABLE user');
    }
}
