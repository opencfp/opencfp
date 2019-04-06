<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

use Phinx\Migration\AbstractMigration;

final class UseNonNullableBoolean extends AbstractMigration
{
    public function change()
    {
        $tableName = 'users';

        $columnNames = [
            'hotel',
            'transportation',
        ];

        $table = $this->table($tableName);

        foreach ($columnNames as $columnName) {
            $sql = <<<SQL
UPDATE users SET $columnName = 0 WHERE $columnName IS NULL;
SQL;
            $this->query($sql);

            $table->changeColumn($columnName, 'boolean', [
                'default' => 0,
                'null'    => false,
            ]);
        }
    }
}
