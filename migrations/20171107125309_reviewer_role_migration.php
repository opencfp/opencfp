<?php

use Phinx\Migration\AbstractMigration;

class ReviewerRoleMigration extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO groups (name, permissions, created_at, updated_at) VALUES ('Reviewer', '{\"reviewer\":1}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    }

    public function down()
    {
        $this->execute("DELETE FROM groups WHERE name ='Reviewer'");
    }
}
