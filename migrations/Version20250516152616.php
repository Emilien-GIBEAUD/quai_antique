<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516152616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE picture ADD restaurant_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_16DB4F89B1E7706E ON picture (restaurant_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F89B1E7706E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_16DB4F89B1E7706E ON picture
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE picture DROP restaurant_id
        SQL);
    }
}
