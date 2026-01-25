<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122122106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staff_specialization
                            (id INT AUTO_INCREMENT NOT NULL,
                            staff_id INT NOT NULL,
                            specialization_id INT NOT NULL,
                            INDEX IDX_DDF3F77DD4D57CD (staff_id),
                            INDEX IDX_DDF3F77DFA846217 (specialization_id),
                            PRIMARY KEY(id))
                            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DD4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DFA846217 FOREIGN KEY (specialization_id) REFERENCES specialization (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DD4D57CD');
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DFA846217');
        $this->addSql('DROP TABLE staff_specialization');
    }
}
