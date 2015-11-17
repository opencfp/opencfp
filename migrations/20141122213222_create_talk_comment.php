<?php

use Phinx\Migration\AbstractMigration;

class CreateTalkComment extends AbstractMigration
{
    /**
     * Create Talk Comment Table
     */
    public function change()
    {
        $this->table('talk_comments')
            ->addColumn('user_id', 'integer')
            ->addColumn('talk_id', 'integer')
            ->addColumn('message', 'text')
            ->addColumn('created', 'datetime')
            ->addIndex(['user_id', 'talk_id'], ['name' => 'talk_comment--user_id__talk_id'])
            ->create();
    }
}