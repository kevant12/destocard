<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618161405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE orders_products (id INT AUTO_INCREMENT NOT NULL, orders_id INT DEFAULT NULL, INDEX IDX_749C879CCFFE9AD6 (orders_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders_products ADD CONSTRAINT FK_749C879CCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media CHANGE video_url video_url VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages CHANGE expedition_date expedition_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE delivery_at delivery_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ADD orders_products_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A1F335533 FOREIGN KEY (orders_products_id) REFERENCES orders_products (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B3BA5A5A1F335533 ON products (orders_products_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD civility VARCHAR(10) NOT NULL, ADD is_verified TINYINT(1) DEFAULT NULL, ADD verification_token VARCHAR(100) DEFAULT NULL, ADD verification_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD reset_token VARCHAR(100) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE phone_number phone_number VARCHAR(20) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A1F335533
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders_products DROP FOREIGN KEY FK_749C879CCFFE9AD6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orders_products
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE delivery_at delivery_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B3BA5A5A1F335533 ON products
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products DROP orders_products_id, CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP civility, DROP is_verified, DROP verification_token, DROP verification_token_expires_at, DROP reset_token, DROP reset_token_expires_at, CHANGE phone_number phone_number INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media CHANGE video_url video_url VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages CHANGE expedition_date expedition_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
