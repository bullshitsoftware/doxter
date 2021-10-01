<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211001164834 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN settings_timezone VARCHAR(32) DEFAULT \'UTC\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (
            id BLOB NOT NULL --(DC2Type:uuid)
            , email VARCHAR(180) NOT NULL
            , password VARCHAR(255) NOT NULL
            , PRIMARY KEY(id)
        )');
        $this->addSql('INSERT INTO user (id, email, password) SELECT id, email, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
