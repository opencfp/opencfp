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
            ->changeColumn('desired', 'boolean', ['default' => false])
            ->changeColumn('favorite', 'boolean', ['default' => false])
            ->changeColumn('sponsor', 'boolean', ['default' => false])
            ->changeColumn('selected', 'boolean', ['default' => false])
            ->changeColumn('created_at', 'datetime', ['default' => null])
            ->changeColumn('updated_at', 'datetime', ['default' => null])
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
