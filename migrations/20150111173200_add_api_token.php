<?php

use Phinx\Migration\AbstractMigration;

class AddApiToken extends AbstractMigration
{
    public function change()
    {
        $users = $this->table('users');
        $users->addColumn('api_token', 'string', array('limit' => 32));
        $users->save();

        // If a user doesn't already have an API token, create one for them
        $rows = $this->fetchAll('SELECT * FROM users');

        foreach ($rows as $row) {
            $api_token = md5(time() . $row['id'] . trim($row['email']));
            $sql = "UPDATE users SET api_token = '{$api_token}' WHERE id = {$row['id']}";
            $this->execute($sql);
        }
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
        $users = $this->table('users');
        $users->dropColumn('api_token');
    }
}
