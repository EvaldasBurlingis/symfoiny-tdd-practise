<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210219071045 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE stock_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE stock (id INT NOT NULL, symbol VARCHAR(8) NOT NULL, short_name VARCHAR(150) NOT NULL, currency VARCHAR(4) NOT NULL, exchange_name VARCHAR(100) NOT NULL, region VARCHAR(100) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, price_change NUMERIC(10, 2) DEFAULT NULL, previous_close NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE stock_id_seq CASCADE');
        $this->addSql('DROP TABLE stock');
    }
}
