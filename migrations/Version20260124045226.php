<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124045226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment
                            (id INT AUTO_INCREMENT NOT NULL,
                            user_id INT NOT NULL,
                            staff_id INT DEFAULT NULL,
                            status_id INT NOT NULL,
                            service_id INT DEFAULT NULL,
                            result LONGTEXT DEFAULT NULL,
                            date DATETIME NOT NULL,
                            INDEX IDX_FE38F844A76ED395 (user_id),
                            INDEX IDX_FE38F844D4D57CD (staff_id),
                            INDEX IDX_FE38F8446BF700BD (status_id),
                            INDEX IDX_FE38F844ED5CA9E6 (service_id),
                            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8446BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844A76ED395');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844D4D57CD');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8446BF700BD');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844ED5CA9E6');
        $this->addSql('DROP TABLE appointment');
    }
}
