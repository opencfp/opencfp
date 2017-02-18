<?php

use Phinx\Migration\AbstractMigration;

class SentinelThrottleUpdates extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('throttle');
        $table->addColumn('ip', 'string', ['null' => true])
            ->addColumn('type', 'string')
            ->addColumn('created_at', 'timestamp', ['default' => 'NOW()'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'NOW()'])
            ->removeColumn('ip_address')
            ->removeColumn('attempts')
            ->removeColumn('suspended')
            ->removeColumn('last_attempt_at')
            ->removeColumn('suspended_at')
            ->removeColumn('banned_at')
            ->update();
    }
}
