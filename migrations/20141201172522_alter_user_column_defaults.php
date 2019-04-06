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

class AlterUserColumnDefaults extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('users')
            ->changeColumn('activated', 'boolean', ['default' => false, 'null' => true])
            ->changeColumn('activation_code', 'string', ['null' => true])
            ->changeColumn('activated_at', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('last_login', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('persist_code', 'string', ['null' => true])
            ->changeColumn('reset_password_code', 'string', ['null' => true])
            ->changeColumn('first_name', 'string', ['null' => true])
            ->changeColumn('last_name', 'string', ['null' => true])
            ->changeColumn('created_at', 'datetime', ['default' => null])
            ->changeColumn('updated_at', 'datetime', ['default' => null])
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
