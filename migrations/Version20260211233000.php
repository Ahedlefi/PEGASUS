<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211233000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add forgot-password token fields on user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD reset_password_token VARCHAR(100) DEFAULT NULL, ADD reset_password_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_8D93D649F78C80BD ON user (reset_password_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_8D93D649F78C80BD ON user');
        $this->addSql('ALTER TABLE user DROP reset_password_token, DROP reset_password_expires_at');
    }
}
