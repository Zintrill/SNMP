<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250119173040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    // 1️⃣ Dodaj kolumnę `email`, ale pozwól na NULL (tymczasowo)
    $this->addSql('ALTER TABLE users ADD email VARCHAR(180) DEFAULT NULL');

    // 2️⃣ Wypełnij istniejące rekordy domyślną wartością (np. `unknown@example.com`)
    $this->addSql("UPDATE users SET email = 'unknown@example.com' WHERE email IS NULL");

    // 3️⃣ Ustaw `NOT NULL` (teraz migracja przejdzie poprawnie)
    $this->addSql('ALTER TABLE users ALTER COLUMN email SET NOT NULL');

    // 4️⃣ Dodaj unikalny indeks dla `email`
    $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
}


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE users DROP email');
        $this->addSql('ALTER TABLE users ALTER fullname SET NOT NULL');
    }
}
