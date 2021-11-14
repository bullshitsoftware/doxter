<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211105223342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (
            id BLOB NOT NULL --(DC2Type:uuid)
            , email VARCHAR(180) NOT NULL
            , password VARCHAR(255) NOT NULL
            , settings_timezone VARCHAR(32) NOT NULL
            , settings_date_format VARCHAR(16) NOT NULL
            , settings_date_time_format VARCHAR(16) NOT NULL
            , settings_weights CLOB NOT NULL --(DC2Type:json)
            , PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
    }
}
