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

class Favorites extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('favorites');
        $table->addColumn('admin_user_id', 'integer')
            ->addColumn('talk_id', 'integer')
            ->addColumn('created', 'datetime')
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('favorites')
            ->removeColumn('admin_user_id')
            ->removeColumn('talk_id')
            ->removeColumn('created')
            ->save();
    }
}
