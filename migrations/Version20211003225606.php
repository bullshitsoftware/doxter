<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211003225606 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tag (
            id BLOB NOT NULL --(DC2Type:uuid)
            , user_id BLOB NOT NULL --(DC2Type:uuid)
            , name VARCHAR(64) NOT NULL
            , PRIMARY KEY(id)
            , CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
                NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON tag (user_id)');
        $this->addSql('CREATE UNIQUE INDEX tag_unique ON tag (user_id, name)');

        $this->addSql('CREATE TABLE task_tag (
            task_id BLOB NOT NULL --(DC2Type:uuid)
            , tag_id BLOB NOT NULL --(DC2Type:uuid)
            , PRIMARY KEY(task_id, tag_id)
            , CONSTRAINT FK_6C0B4F048DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE 
                NOT DEFERRABLE INITIALLY IMMEDIATE
            , CONSTRAINT FK_6C0B4F04BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE 
                NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_6C0B4F048DB60186 ON task_tag (task_id)');
        $this->addSql('CREATE INDEX IDX_6C0B4F04BAD26311 ON task_tag (tag_id)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE task_tag');
    }
}
