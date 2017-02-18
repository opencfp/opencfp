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
    public function up()
    {
        $table = $this->table('throttle');
        $table->addColumn('ip', 'string', ['null' => true])
            ->addColumn('type', 'string')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->removeColumn('ip_address')
            ->removeColumn('attempts')
            ->removeColumn('suspended')
            ->removeColumn('last_attempt_at')
            ->removeColumn('suspended_at')
            ->removeColumn('banned_at')
            ->update();
    }

    public function down()
    {
        $table = $this->table('throttle');
        $table->addColumn('ip_address', 'string')
            ->addColumn('attempts', 'string')
            ->addColumn('suspended', 'string')
            ->addColumn('last_attempt_at', 'string')
            ->addColumn('suspended_at', 'string')
            ->addColumn('banned_at', 'string')
            ->removeColumn('ip')
            ->removeColumn('type')
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->update();
    }
}
