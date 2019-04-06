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

class AddTalkMeta extends AbstractMigration
{
    /**
     * Create Talk Meta Table
     */
    public function change()
    {
        $this->table('talk_meta')
            ->addColumn('admin_user_id', 'integer')
            ->addColumn('talk_id', 'integer')
            ->addColumn('rating', 'integer', ['default' => 0])
            ->addColumn('viewed', 'boolean', ['default' => false])
            ->addColumn('created', 'datetime')
            ->addIndex(['admin_user_id', 'talk_id'], ['name' => 'talk_meta__admin_user_id__talk_id', 'unique' => true])
            ->create();
    }
}
