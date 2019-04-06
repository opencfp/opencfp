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

class AlterTalkColumnsAllowNull extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('talks')
            ->changeColumn('other', 'text', ['null' => true])
            ->changeColumn('slides', 'string', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('talks')
            ->changeColumn('other', 'text')
            ->changeColumn('slides', 'string')
            ->save();
    }
}
