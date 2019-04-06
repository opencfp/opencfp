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

class CreateTalkComment extends AbstractMigration
{
    /**
     * Create Talk Comment Table
     */
    public function change()
    {
        $this->table('talk_comments')
            ->addColumn('user_id', 'integer')
            ->addColumn('talk_id', 'integer')
            ->addColumn('message', 'text')
            ->addColumn('created', 'datetime')
            ->addIndex(['user_id', 'talk_id'], ['name' => 'talk_comment__user_id__talk_id'])
            ->create();
    }
}
