<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use LogicException;

final class Version20211030140346 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS
            SELECT id
                , user_id
                , title
                , (
                    SELECT json_group_array(ta.name)
                    FROM tag ta JOIN task_tag tt ON ta.id = tt.tag_id
                    WHERE tt.task_id = t.id
                ) as tags
                , description
                , created
                , updated
                , wait
                , started
                , ended
                , due
            FROM task t
        ');
        $this->addSql('DROP TABLE task_tag');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE tag');

        $this->addSql('CREATE TABLE task (
            id BLOB NOT NULL --(DC2Type:uuid)
            , user_id BLOB NOT NULL --(DC2Type:uuid)
            , tags CLOB NOT NULL --(DC2Type:json)
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
        $this->addSql('INSERT INTO task (id, user_id, tags, title, description, created, updated, wait, started, ended)
            SELECT id, user_id, tags, title, description, created, updated, wait, started, ended
            FROM __temp__task
        ');
        $this->addSql('DROP TABLE __temp__task');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        throw new LogicException('Not supproted.');
    }
}
