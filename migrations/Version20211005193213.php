<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211005193213 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN settings_date_format VARCHAR(16) DEFAULT \'Y-m-d\' NOT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN settings_date_time_format VARCHAR(16) DEFAULT \'Y-m-d H:i:s\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, password, settings_timezone FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (
            id BLOB NOT NULL --(DC2Type:uuid)
            , email VARCHAR(180) NOT NULL
            , password VARCHAR(255) NOT NULL
            , settings_timezone VARCHAR(32) DEFAULT \'UTC\' NOT NULL
            , PRIMARY KEY(id)
        )');
        $this->addSql('INSERT INTO user (id, email, password, settings_timezone) 
            SELECT id, email, password, settings_timezone FROM __temp__user
        ');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
