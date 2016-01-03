<?php

use Phinx\Migration\AbstractMigration;

class AllowDateTimeFieldsToBeNull extends AbstractMigration
{
    public function up()
    {
        $options = [
            'null' => true,
            'default' => null,
        ];

        $talks = $this->table('talks');

        $talks->changeColumn('created_at', 'datetime', $options);
        $talks->changeColumn('updated_at', 'datetime', $options);

        $talks->save();

        $users = $this->table('users');

        $users->changeColumn('activated_at', 'datetime', $options);
        $users->changeColumn('created_at', 'datetime', $options);
        $users->changeColumn('updated_at', 'datetime', $options);
        $users->changeColumn('last_login', 'datetime', $options);

        $users->save();
    }

    public function down()
    {
        $talks = $this->table('talks');

        $talks->changeColumn('created_at', 'datetime', [
            'null' => false,
            'default' => '1000-01-01 00:00:00',
        ]);

        $talks->changeColumn('updated_at', 'datetime', [
            'null' => false,
            'default' => '1000-01-01 00:00:00',
        ]);

        $talks->save();

        $users = $this->table('users');

        $users->changeColumn('activated_at', 'datetime', [
            'null' => true,
            'default' => '1000-01-01 00:00:00',
        ]);

        $users->changeColumn('created_at', 'datetime', [
            'null' => false,
            'default' => '1000-01-01 00:00:00',
        ]);

        $users->changeColumn('updated_at', 'datetime', [
            'null' => false,
            'default' => '1000-01-01 00:00:00',
        ]);

        $users->changeColumn('last_login', 'datetime', [
            'null' => true,
            'default' => '1000-01-01 00:00:00',
        ]);

        $users->save();
    }
}
