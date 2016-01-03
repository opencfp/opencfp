<?php

use Phinx\Migration\AbstractMigration;

class SetDateTimeFieldsWithPreviousDefaultToNull extends AbstractMigration
{
    /**
     * @var array
     */
    private $tables = [
        'talks' => [
            'created_at',
            'updated_at',
        ],
        'users' => [
            'activated_at',
            'created_at',
            'updated_at',
            'last_login',
        ],
    ];

    public function up()
    {
        $this->run('UPDATE `%s` SET `%s` = NULL WHERE `%s` = "1000-01-01 00:00:00"');
    }

    public function down()
    {
        $this->run('UPDATE `%s` SET `%s` = "1000-01-01 00:00:00" WHERE `%s` IS NULL');
    }

    private function run($sql)
    {
        array_walk($this->tables, function ($columnNames, $tableName) use ($sql) {
            array_walk($columnNames, function ($columnName) use ($tableName, $sql) {
                $this->execute(sprintf(
                    $sql,
                    $tableName,
                    $columnName,
                    $columnName
                ));
            });
        });
    }
}
