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

/**
 * This migration is for setting up the database conform to Sentinel
 * It will not change any data.
 */
class SentinelMigration extends AbstractMigration
{
    public function up()
    {
        $this->activationsTable();
        $this->persistencesTable();
        $this->remindersTable();
        $this->rolesTable();
        $this->roleUsersTable();
        $this->throttleUp();
    }

    public function down()
    {
        $this->dropTable('activations');
        $this->dropTable('persistences');
        $this->dropTable('reminders');
        $this->dropTable('roles');
        $this->dropTable('role_users');
        $this->throttleDown();
    }

    private function activationsTable()
    {
        $this->table('activations')
            ->addColumn('user_id', 'integer')
            ->addColumn('code', 'string')
            ->addColumn('completed', 'boolean', ['default' => 0])
            ->addColumn('completed_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->create();
    }

    private function persistencesTable()
    {
        $this->table('persistences')
            ->addColumn('user_id', 'integer')
            ->addColumn('code', 'string')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->create();
    }

    private function remindersTable()
    {
        $this->table('reminders')
            ->addColumn('user_id', 'integer')
            ->addColumn('code', 'string')
            ->addColumn('completed', 'boolean', ['default' => 0])
            ->addColumn('completed_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->create();
    }

    private function rolesTable()
    {
        $this->table('roles')
            ->addColumn('slug', 'string')
            ->addColumn('name', 'string')
            ->addColumn('permissions', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addIndex(['slug'], ['name' => 'roles_slug_unique', 'unique' => true, 'limit' => 191])

            ->create();
    }

    private function roleUsersTable()
    {
        $this->table('role_users', ['id' => false, 'primary_key' => ['user_id', 'role_id']])
            ->addColumn('user_id', 'integer')
            ->addColumn('role_id', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->create();
    }

    private function throttleUp()
    {
        $this->table('throttle')
            ->renameColumn('ip_address', 'ip')
            ->changeColumn('ip', 'string', ['null' => true])
            ->changeColumn('user_id', 'integer', ['null' => true])
            ->addColumn('type', 'string')
            ->update();
    }

    private function throttleDown()
    {
        $this->table('throttle')
            ->renameColumn('ip', 'ip_address')
            ->removeColumn('type')
            ->update();
    }
}
