<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

use Phinx\Migration\AbstractMigration;

class Schema extends AbstractMigration
{
    public function change()
    {
        $this->createUsersTable();
        $this->createGroupsTable();
        $this->createUsersGroupsTable();
        $this->createThrottleTable();
        $this->createSpeakersTable();
        $this->createTalksTable();
    }

    protected function createGroupsTable()
    {
        $this->table('groups')
            ->addColumn('name', 'string')
            ->addColumn('permissions', 'text')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->addIndex(['name'], ['name' => 'groups_name_unique', 'unique' => true, 'limit' => 191])
            ->create();

        $this->execute("INSERT INTO groups (name, permissions, created_at, updated_at) VALUES ('Speakers', '{\"users\":1}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $this->execute("INSERT INTO groups (name, permissions, created_at, updated_at) VALUES ('Admin', '{\"admin\":1}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    }

    protected function createThrottleTable()
    {
        $this->table('throttle')
            ->addColumn('user_id', 'integer')
            ->addColumn('ip_address', 'string')
            ->addColumn('attempts', 'integer', ['limit' => 4, 'default' => 0])
            ->addColumn('suspended', 'integer', ['limit' => 4, 'default' => 0])
            ->addColumn('banned', 'integer', ['limit' => 4, 'default' => 0])
            ->addColumn('last_attempt_at', 'datetime')
            ->addColumn('suspended_at', 'datetime')
            ->addColumn('banned_at', 'datetime')
            ->create();
    }

    protected function createUsersTable()
    {
        $this->table('users')
            ->addColumn('email', 'string')
            ->addColumn('password', 'string')
            ->addColumn('permissions', 'text')
            ->addColumn('activated', 'boolean')
            ->addColumn('activation_code', 'string')
            ->addColumn('persist_code', 'string')
            ->addColumn('reset_password_code', 'string')
            ->addColumn('first_name', 'string')
            ->addColumn('last_name', 'string')
            ->addColumn('company', 'string')
            ->addColumn('twitter', 'string')
            ->addColumn('airport', 'string')
            ->addColumn('url', 'string')
            ->addColumn('activated_at', 'datetime', ['null' => true])
            ->addColumn('last_login', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['email'], ['name' => 'users_email_unique', 'unique' => true, 'limit' => 191])
            ->create();
    }

    protected function createUsersGroupsTable()
    {
        $this->table('users_groups')
            ->addColumn('user_id', 'integer')
            ->addColumn('group_id', 'integer')
            ->create();
    }

    protected function createSpeakersTable()
    {
        $this->table('speakers', ['id' => false, 'primary_key' => 'user_id'])
            ->addColumn('user_id', 'integer')
            ->addColumn('info', 'text')
            ->addColumn('bio', 'text')
            ->addColumn('hotel', 'boolean')
            ->addColumn('photo_path', 'text')
            ->create();
    }

    protected function createTalksTable()
    {
        $this->table('talks')
            ->addColumn('user_id', 'integer')
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('description', 'text')
            ->addColumn('other', 'text')
            ->addColumn('type', 'string', ['limit' => 50])
            ->addColumn('level', 'string', ['limit' => 50])
            ->addColumn('category', 'string', ['limit' => 50])
            ->addColumn('slides', 'string')
            ->addColumn('desired', 'boolean')
            ->addColumn('sponsor', 'boolean')
            ->addColumn('favorite', 'boolean')
            ->addColumn('selected', 'boolean')
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->create();
    }
}
