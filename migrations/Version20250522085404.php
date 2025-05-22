<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522085404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant ADD user_id INT NOT NULL, ADD uuid CHAR(36) NOT NULL COMMENT '(DC2Type:guid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_EB95123FA76ED395 ON restaurant (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD uuid CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', ADD first_name VARCHAR(32) NOT NULL, ADD last_name VARCHAR(64) NOT NULL, ADD guest_number SMALLINT DEFAULT NULL, ADD allergy VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E00CEDDEA76ED395 ON booking
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_EB95123FA76ED395 ON restaurant
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE restaurant DROP user_id, DROP uuid
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP uuid, DROP first_name, DROP last_name, DROP guest_number, DROP allergy
        SQL);
    }
}
