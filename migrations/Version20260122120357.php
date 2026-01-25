<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122120357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staff
                            (id INT AUTO_INCREMENT NOT NULL,
                            clinic_id INT NOT NULL,
                            cabinet_id INT DEFAULT NULL,
                            first_name VARCHAR(35) NOT NULL,
                            last_name VARCHAR(35) NOT NULL,
                            patronymic VARCHAR(35) NOT NULL,
                            experience DATE NOT NULL,
                            phone VARCHAR(15) NOT NULL,
                            avatar_path VARCHAR(255) DEFAULT NULL,
                            INDEX IDX_426EF392CC22AD4 (clinic_id),
                            INDEX IDX_426EF392D351EC (cabinet_id),
                            PRIMARY KEY(id))
                            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392CC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392D351EC FOREIGN KEY (cabinet_id) REFERENCES cabinet (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392CC22AD4');
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392D351EC');
        $this->addSql('DROP TABLE staff');
    }
}
