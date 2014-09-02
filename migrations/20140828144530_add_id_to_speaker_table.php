<?php

use Phinx\Migration\AbstractMigration;

class AddIdToSpeakerTable extends AbstractMigration
{
    public function change()
    {
        $this->execute('ALTER TABLE speakers DROP PRIMARY KEY');
        $this->execute('ALTER TABLE speakers ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
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
