<?php

use Phinx\Migration\AbstractMigration;

class FixZeroDates extends AbstractMigration
{
    private $tables = [
        'talks' => [
            'created_at',
            'updated_at'
        ],
        'users' => [
            'activated_at',
            'created_at',
            'updated_at',
            'last_login',
        ]
    ];

    public function up()
    {
        $this->execute("SET SESSION sql_mode = ''");

        $this->table('users')
            ->changeColumn('activated_at', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('last_login', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->save();

        $this->table('talks')
            ->changeColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->changeColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->save();

        $this->run('UPDATE IGNORE `%s` SET `%s` = NULL WHERE `%s` IN ("0000-00-00 00:00:00", "1000-01-01 00:00:00")');
    }

    public function down()
    {
        $this->run('UPDATE IGNORE `%s` SET `%s` = "1000-01-01 00:00:00" WHERE `%s` IS NULL');

        $this->table('users')
            ->changeColumn('activated_at', 'string', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->changeColumn('last_login', 'string', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->changeColumn('created_at', 'datetime', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->changeColumn('updated_at', 'datetime', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->save();

        $this->table('talks')
            ->changeColumn('created_at', 'datetime', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->changeColumn('updated_at', 'datetime', ['null' => false, 'default' => '1000-01-01 00:00:00'])
            ->save();
    }

    private function run($sql)
    {
        array_walk($this->tables, function ($columnNames, $tableName) use ($sql) {
            array_walk($columnNames, function ($columnName) use ($tableName, $sql) {
                $this->execute(sprintf(
                    $sql,
                    $tableName,
                    $columnName,
                    $columnName,
                    $columnName
                ));
            });
        });
    }
}
