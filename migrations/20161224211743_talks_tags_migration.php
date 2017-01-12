<?php

use Phinx\Migration\AbstractMigration;

class TalksTagsMigration extends AbstractMigration
{
    public function up()
    {
        $this->table('tags')
            ->addColumn('tag', 'string', ['limit' => 50])
            ->create();

        $this->table('talks_tags', ['id' => false])
            ->addColumn('talk_id', 'integer')
            ->addColumn('tag_id', 'integer')
            ->create();
    }

    public function down()
    {
        $this->dropTable('talks_tags');
        $this->dropTable('tags');
    }
}
