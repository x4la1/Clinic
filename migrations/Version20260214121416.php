<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260214121416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staff_time_slot (id INT AUTO_INCREMENT NOT NULL, slot_id INT NOT NULL, staff_id INT NOT NULL, INDEX IDX_30FFF90559E5119C (slot_id), INDEX IDX_30FFF905D4D57CD (staff_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_slot (id INT AUTO_INCREMENT NOT NULL, slot TIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE staff_time_slot ADD CONSTRAINT FK_30FFF90559E5119C FOREIGN KEY (slot_id) REFERENCES time_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE staff_time_slot ADD CONSTRAINT FK_30FFF905D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff_time_slot DROP FOREIGN KEY FK_30FFF90559E5119C');
        $this->addSql('ALTER TABLE staff_time_slot DROP FOREIGN KEY FK_30FFF905D4D57CD');
        $this->addSql('DROP TABLE staff_time_slot');
        $this->addSql('DROP TABLE time_slot');
    }
}
