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

class TalksTagsMigration extends AbstractMigration
{
    public function up()
    {
        $this->table('tags')
            ->addColumn('tag', 'string', ['limit' => 50])
            ->create();

        $this->table('talks_tags', ['id' => false])
            ->addColumn('talk_id', 'integer')
            ->addColumn('tag_id', 'integer')
            ->create();
    }

    public function down()
    {
        $this->dropTable('talks_tags');
        $this->dropTable('tags');
    }
}
