<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122123724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staff_service
                            (id INT AUTO_INCREMENT NOT NULL,
                            staff_id INT NOT NULL,
                            service_id INT NOT NULL,
                            INDEX IDX_BD2B8D64D4D57CD (staff_id),
                            INDEX IDX_BD2B8D64ED5CA9E6 (service_id),
                            PRIMARY KEY(id))
                            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_BD2B8D64D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE staff_service ADD CONSTRAINT FK_BD2B8D64ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_BD2B8D64D4D57CD');
        $this->addSql('ALTER TABLE staff_service DROP FOREIGN KEY FK_BD2B8D64ED5CA9E6');
        $this->addSql('DROP TABLE staff_service');
    }
}
