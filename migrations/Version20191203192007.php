<?php

declare(strict_types=1);

namespace OpenCFP\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203192007 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Modify user roles';
    }

    public function up(Schema $schema) : void
    {
        $speakerPermissions  = '{"talk.update":true,"talk.review":false,"user.delete":false}';
        $reviewerPermissions = '{"talk.update":true,"talk.review":true,"user.delete":false}';
        $adminPermissions    = '{"talk.update":true,"talk.review":true,"user.delete":true}';
        $roleData            = [
            ['name' => 'Speaker', 'slug' => 'speaker', 'permissions' => $speakerPermissions],
            ['name' => 'Reviewer', 'slug' => 'reviewer', 'permissions' => $reviewerPermissions],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => $adminPermissions],
        ];

        foreach ($roleData as $role) {
            $this->addSql('INSERT INTO roles (name, slug, permissions) VALUES(:name, :slug, :permissions)', $role);
        }
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('TRUNCATE roles');
    }
}
