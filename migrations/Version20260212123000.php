<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove phone country from user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP phone_country');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD phone_country VARCHAR(2) DEFAULT NULL');
    }
}
