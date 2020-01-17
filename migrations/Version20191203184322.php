<?php

declare(strict_types=1);

namespace OpenCFP\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use phpDocumentor\Reflection\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203184322 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('activations');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('completed', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('completed_at', 'datetime', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('airports');
        $table->addColumn('code', 'string', ['length' => 3, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('country', 'string', ['length' => 255]);
        $table->setPrimaryKey(['code']);

        $table = $schema->createTable('favorites');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('admin_user_id', 'integer', ['notnull' => true]);
        $table->addColumn('talk_id', 'integer', ['notnull' => true]);
        $table->addColumn('created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('groups');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('permissions', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('persistences');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('reminders');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('completed', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('completed_at', 'datetime', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('role_users');
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('role_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['user_id', 'role_id']);

        $table = $schema->createTable('roles');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('slug', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('permissions', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => false]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('talk_comments');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('talk_id', 'integer', ['notnull' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('message', 'text', ['notnull' => true]);
        $table->addColumn('created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('talk_meta');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('admin_user_id', 'integer', ['notnull' => true]);
        $table->addColumn('talk_id', 'integer', ['notnull' => true]);
        $table->addColumn('rating', 'integer', ['default' => 0]);
        $table->addColumn('viewed', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('talks');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('title', 'string', ['length' => 100, 'notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => true]);
        $table->addColumn('other', 'text', ['notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 50, 'notnull' => true]);
        $table->addColumn('level', 'string', ['length' => 50, 'notnull' => true]);
        $table->addColumn('category', 'string', ['length' => 50, 'notnull' => true]);
        $table->addColumn('slides', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('desired', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('sponsor', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('favorite', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('selected', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('throttle');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('ip', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('attempts', 'integer', ['default' => 0, 'notnull' => true]);
        $table->addColumn('suspended', 'integer', ['default' => 0, 'notnull' => true]);
        $table->addColumn('banned', 'integer', ['default' => 0, 'notnull' => true]);
        $table->addColumn('last_attempt_at', 'datetime', ['notnull' => true]);
        $table->addColumn('suspended_at', 'datetime', ['notnull' => true]);
        $table->addColumn('banned_at', 'datetime', ['notnull' => true]);
        $table->addColumn('type', 'string', ['length' => 255, 'notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('users');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('password', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('permissions', 'text', ['notnull' => false]);
        $table->addColumn('activated', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('activation_code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('persist_code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('reset_password_code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('first_name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('last_name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('company', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('twitter', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('airport', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('url', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('activated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('last_login', 'datetime', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => false]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('transportation', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('hotel', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('info', 'text', ['notnull' => false]);
        $table->addColumn('bio', 'text', ['notnull' => false]);
        $table->addColumn('photo_path', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('has_made_profile', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('joindin_username', 'string', ['length' => 255, 'notnull' => false]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('users_groups');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('group_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('activations');
        $schema->dropTable('airports');
        $schema->dropTable('favorites');
        $schema->dropTable('groups');
        $schema->dropTable('persistences');
        $schema->dropTable('phinxlog');
        $schema->dropTable('reminders');
        $schema->dropTable('role_users');
        $schema->dropTable('roles');
        $schema->dropTable('talk_comments');
        $schema->dropTable('talk_meta');
        $schema->dropTable('talks');
        $schema->dropTable('throttle');
        $schema->dropTable('users');
        $schema->dropTable('users_groups');
    }
}
