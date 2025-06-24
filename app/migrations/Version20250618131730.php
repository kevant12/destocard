<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618131730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE users_products (users_id INT NOT NULL, products_id INT NOT NULL, INDEX IDX_C8FB718067B3B43D (users_id), INDEX IDX_C8FB71806C8A81A9 (products_id), PRIMARY KEY(users_id, products_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_products ADD CONSTRAINT FK_C8FB718067B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_products ADD CONSTRAINT FK_C8FB71806C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD sender_id INT DEFAULT NULL, ADD receper_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E9682EEBCEB FOREIGN KEY (receper_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DB021E96F624B39D ON messages (sender_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DB021E9682EEBCEB ON messages (receper_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users_products DROP FOREIGN KEY FK_C8FB718067B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_products DROP FOREIGN KEY FK_C8FB71806C8A81A9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_products
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9682EEBCEB
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_DB021E96F624B39D ON messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_DB021E9682EEBCEB ON messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP sender_id, DROP receper_id
        SQL);
    }
}
