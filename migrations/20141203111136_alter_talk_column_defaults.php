<?php

use Phinx\Migration\AbstractMigration;

class AlterTalkColumnDefaults extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('talks')
            ->changeColumn('desired', 'boolean', ['default' => 0])
            ->changeColumn('favorite', 'boolean', ['default' => 0])
            ->changeColumn('sponsor', 'boolean', ['default' => 0])
            ->changeColumn('selected', 'boolean', ['default' => 0])
            ->changeColumn('created_at', 'datetime', ['default' => 'null'])
            ->changeColumn('updated_at', 'datetime', ['default' => 'null'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('talks')
            ->changeColumn('desired', 'boolean')
            ->changeColumn('favorite', 'boolean')
            ->changeColumn('sponsor', 'boolean')
            ->changeColumn('selected', 'boolean')
            ->changeColumn('created_at', 'datetime')
            ->changeColumn('updated_at', 'datetime')
            ->save();
    }
}