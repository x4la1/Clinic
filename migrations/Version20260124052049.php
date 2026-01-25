<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124052049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392D351EC');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392D351EC FOREIGN KEY (cabinet_id) REFERENCES cabinet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DD4D57CD');
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DFA846217');
        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DD4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DFA846217 FOREIGN KEY (specialization_id) REFERENCES specialization (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF392D351EC');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF392D351EC FOREIGN KEY (cabinet_id) REFERENCES cabinet (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DD4D57CD');
        $this->addSql('ALTER TABLE staff_specialization DROP FOREIGN KEY FK_DDF3F77DFA846217');
        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DD4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE staff_specialization ADD CONSTRAINT FK_DDF3F77DFA846217 FOREIGN KEY (specialization_id) REFERENCES specialization (id) ON UPDATE NO ACTION');
    }
}
