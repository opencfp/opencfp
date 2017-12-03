<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

use Phinx\Migration\AbstractMigration;

final class LimitAirportToThreeCharacters extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');

        $table->changeColumn('airport', 'string', [
            'length' => 3,
            'null'   => true,
        ]);
    }
}
