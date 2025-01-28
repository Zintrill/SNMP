<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250128011212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE device (id SERIAL NOT NULL, device_type_id INT NOT NULL, snmp_version_id INT NOT NULL, device_name VARCHAR(255) NOT NULL, address_ip VARCHAR(45) NOT NULL, user_name VARCHAR(100) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, mac_address VARCHAR(17) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92FB68EB728E969 ON device (mac_address)');
        $this->addSql('CREATE INDEX IDX_92FB68E4FFA550E ON device (device_type_id)');
        $this->addSql('CREATE INDEX IDX_92FB68EA95134DE ON device (snmp_version_id)');
        $this->addSql('CREATE TABLE device_type (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E782135E237E06 ON device_type (name)');
        $this->addSql('CREATE TABLE snmp_version (id SERIAL NOT NULL, version VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_506F8997BF1CD3C3 ON snmp_version (version)');
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, username VARCHAR(50) NOT NULL, fullname VARCHAR(100) DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E4FFA550E FOREIGN KEY (device_type_id) REFERENCES device_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EA95134DE FOREIGN KEY (snmp_version_id) REFERENCES snmp_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E4FFA550E');
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68EA95134DE');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE device_type');
        $this->addSql('DROP TABLE snmp_version');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
