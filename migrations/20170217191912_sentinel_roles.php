<?php

use Phinx\Migration\AbstractMigration;

class SentinelRoles extends AbstractMigration
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
        $table = $this->table('roles');
        $table->addColumn('slug', 'string')
            ->addColumn('name', 'string')
            ->addColumn('permissions', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => '1970-01-01 00:00:00'])
            ->addColumn('upated_at', 'timestamp', ['default' => '1970-01-01 00:00:00'])
            ->create();
    }
}
