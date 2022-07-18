<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718142720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout ADD status_id INT NOT NULL, ADD organizer_id INT NOT NULL, ADD campus_organizer_site_id INT NOT NULL, ADD place_id INT NOT NULL');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31ECBDF620 FOREIGN KEY (campus_organizer_site_id) REFERENCES campus (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31EDA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E6BF700BD ON hangout (status_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E876C4DDA ON hangout (organizer_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31ECBDF620 ON hangout (campus_organizer_site_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31EDA6A219 ON hangout (place_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E6BF700BD');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E876C4DDA');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31ECBDF620');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31EDA6A219');
        $this->addSql('DROP INDEX IDX_20C5B31E6BF700BD ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31E876C4DDA ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31ECBDF620 ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31EDA6A219 ON hangout');
        $this->addSql('ALTER TABLE hangout DROP status_id, DROP organizer_id, DROP campus_organizer_site_id, DROP place_id');
    }
}
