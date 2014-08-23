<?php

use Phinx\Migration\AbstractMigration;

class Favorites extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('favorites', array('id' => false));
        $table->addColumn('admin_user_id', 'integer')
            ->addColumn('talk_id', 'integer')
            ->addColumn('created', 'datetime')
            ->create();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
