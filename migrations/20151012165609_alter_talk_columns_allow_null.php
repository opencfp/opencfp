<?php
use Phinx\Migration\AbstractMigration;

class AlterTalkColumnsAllowNull extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('talks')
            ->changeColumn('other', 'text', ['null' => true])
            ->changeColumn('slides', 'string', ['null' => true])
            ->save();
    }
    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('talks')
            ->changeColumn('other', 'text')
            ->changeColumn('slides', 'string')
            ->save();
    }
}
