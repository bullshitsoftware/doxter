<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211105223430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create task table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task (id BLOB NOT NULL --(DC2Type:uuid)
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

        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
