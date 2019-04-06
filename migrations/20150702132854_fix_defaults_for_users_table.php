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

class FixDefaultsForUsersTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('users');

        // Stricter MySQL installs have issues with null or empty string
        // values for columns marked as NOT NULL.
        //
        // Updated with defaults from Sentry:
        //
        // https://github.com/cartalyst/sentry/blob/2.1/schema/mysql.sql
        //
        $users->changeColumn('permissions', 'text', ['null' => true]);
        $users->changeColumn('activated', 'boolean', ['default' => false]);
        $users->changeColumn('activation_code', 'string', ['null' => true]);
        $users->changeColumn('activated_at', 'datetime', ['null' => true, 'default' => '1970-01-01 00:00:00']);
        $users->changeColumn('last_login', 'datetime', ['null' => true, 'default' => '1970-01-01 00:00:00']);
        $users->changeColumn('persist_code', 'string', ['null' => true]);
        $users->changeColumn('reset_password_code', 'string', ['null' => true]);
        $users->changeColumn('first_name', 'string', ['null' => true]);
        $users->changeColumn('last_name', 'string', ['null' => true]);
        $users->changeColumn('created_at', 'datetime', ['default' => '1970-01-01 00:00:00']);
        $users->changeColumn('updated_at', 'datetime', ['default' => '1970-01-01 00:00:00']);

        // Custom fields
        $users->changeColumn('company', 'string', ['null' => true]);
        $users->changeColumn('twitter', 'string', ['null' => true]);
        $users->changeColumn('airport', 'string', ['null' => true]);
        $users->changeColumn('url', 'string', ['null' => true]);
        $users->changeColumn('hotel', 'integer', ['null' => true]);
        $users->changeColumn('transportation', 'integer', ['null' => true]);
        $users->changeColumn('info', 'text', ['null' => true]);
        $users->changeColumn('bio', 'text', ['null' => true]);
        $users->changeColumn('photo_path', 'string', ['null' => true]);

        $users->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $users = $this->table('users');

        $users->changeColumn('photo_path', 'string');
        $users->changeColumn('bio', 'text');
        $users->changeColumn('info', 'text');
        $users->changeColumn('transportation', 'integer');
        $users->changeColumn('hotel', 'integer');
        $users->changeColumn('url', 'string');
        $users->changeColumn('airport', 'string');
        $users->changeColumn('twitter', 'string');
        $users->changeColumn('company', 'string');

        $users->changeColumn('updated_at', 'datetime');
        $users->changeColumn('created_at', 'datetime');
        $users->changeColumn('last_name', 'string');
        $users->changeColumn('first_name', 'string');
        $users->changeColumn('reset_password_code', 'string');
        $users->changeColumn('persist_code', 'string');
        $users->changeColumn('last_login', 'datetime');
        $users->changeColumn('activated_at', 'datetime');
        $users->changeColumn('activation_code', 'string');
        $users->changeColumn('activated', 'boolean');
        $users->changeColumn('permissions', 'text');

        $users->save();
    }
}
