<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210927155628 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task (
            id BLOB NOT NULL --(DC2Type:uuid)
            , user_id BLOB DEFAULT NULL --(DC2Type:uuid)
            , title VARCHAR(144) NOT NULL
            , description CLOB NOT NULL
            , created DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , wait DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , started DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , ended DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
