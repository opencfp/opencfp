<?php

use Phinx\Migration\AbstractMigration;

class AddHotelAndTransportationCostFields extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('transportation', 'integer');
        $table->addColumn('hotel', 'integer');
        $table->save();
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
