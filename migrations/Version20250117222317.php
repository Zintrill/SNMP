<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117222317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop permissions_unique constraint properly with cascade handling';
    }

    public function up(Schema $schema): void
    {
        // Drop foreign key constraints that depend on permissions_unique
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_users_permission');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d649fed90cca');

        // Drop the unique constraint with CASCADE
        $this->addSql('ALTER TABLE permissions DROP CONSTRAINT permissions_unique CASCADE');

        // Drop the index separately (if necessary)
        $this->addSql('DROP INDEX IF EXISTS permissions_unique');

        // Drop the foreign key index from users table (if it exists)
        $this->addSql('DROP INDEX IF EXISTS IDX_1483A5E9FED90CCA');
    }

    public function down(Schema $schema): void
    {
        // Restore the unique constraint
        $this->addSql('ALTER TABLE permissions ADD CONSTRAINT permissions_unique UNIQUE (permission_id)');

        // Restore foreign key constraints
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_users_permission FOREIGN KEY (permission_id) REFERENCES permissions (permission_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d649fed90cca FOREIGN KEY (permission_id) REFERENCES permissions (permission_id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Restore the foreign key index
        $this->addSql('CREATE INDEX IDX_1483A5E9FED90CCA ON users (permission_id)');
    }
}
