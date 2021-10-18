<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211017215851 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_527EDB25A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS
            SELECT id, user_id, title, description, created, updated, wait, started, ended
            FROM task
        ');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (
            id BLOB NOT NULL --(DC2Type:uuid)
            , user_id BLOB NOT NULL --(DC2Type:uuid)
            , title VARCHAR(144) NOT NULL
            , description CLOB NOT NULL
            , created DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , wait DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , started DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , ended DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , due DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id)
            , CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id)
                REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('INSERT INTO task (id, user_id, title, description, created, updated, wait, started, ended)
            SELECT id, user_id, title, description, created, updated, wait, started, ended
            FROM __temp__task
        ');
        $this->addSql('DROP TABLE __temp__task');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_527EDB25A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS
            SELECT id, user_id, title, description, created, updated, wait, started, ended
            FROM task
        ');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (
            id BLOB NOT NULL --(DC2Type:uuid)
            , user_id BLOB NOT NULL --(DC2Type:uuid)
            , title VARCHAR(144) NOT NULL
            , description CLOB NOT NULL
            , created DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , wait DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , started DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , ended DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id)
            , CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id)
                REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('INSERT INTO task (id, user_id, title, description, created, updated, wait, started, ended)
            SELECT id, user_id, title, description, created, updated, wait, started, ended
            FROM __temp__task
        ');
        $this->addSql('DROP TABLE __temp__task');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }
}
