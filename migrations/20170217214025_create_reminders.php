<?php

use Phinx\Migration\AbstractMigration;

class CreateReminders extends AbstractMigration
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
        $table = $this->table('reminders');
        $table->addColumn('user_id', 'integer')
            ->addColumn('code', 'string')
            ->addColumn('completed', 'boolean')
            ->addColumn('completed_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => '1970-01-01 00:00:00'])
            ->addColumn('updated_at', 'timestamp', ['default' => '1970-01-01 00:00:00'])
            ->create();
    }
}
