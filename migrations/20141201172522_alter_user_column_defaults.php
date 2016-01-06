<?php

use Phinx\Migration\AbstractMigration;

class AlterUserColumnDefaults extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('users')
            ->changeColumn('activated', 'boolean', ['default' => 0, 'null' => true])
            ->changeColumn('activation_code', 'string', ['null' => true])
            ->changeColumn('activated_at', 'string', ['null' => true, 'default' => 'null'])
            ->changeColumn('last_login', 'string', ['null' => true, 'default' => 'null'])
            ->changeColumn('persist_code', 'string', ['null' => true])
            ->changeColumn('reset_password_code', 'string', ['null' => true])
            ->changeColumn('first_name', 'string', ['null' => true])
            ->changeColumn('last_name', 'string', ['null' => true])
            ->changeColumn('created_at', 'datetime', ['default' => 'null'])
            ->changeColumn('updated_at', 'datetime', ['default' => 'null'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('users')
            ->changeColumn('activated', 'boolean')
            ->changeColumn('activation_code', 'string')
            ->changeColumn('activated_at', 'string')
            ->changeColumn('last_login', 'string')
            ->changeColumn('persist_code', 'string')
            ->changeColumn('reset_password_code', 'string')
            ->changeColumn('first_name', 'string')
            ->changeColumn('last_name', 'string')
            ->changeColumn('created_at', 'datetime')
            ->changeColumn('updated_at', 'datetime')
            ->save();
    }
}