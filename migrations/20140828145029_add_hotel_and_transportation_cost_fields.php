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

class AddHotelAndTransportationCostFields extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('transportation', 'integer');
        $table->addColumn('hotel', 'integer');
        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('users')
            ->removeColumn('transportation')
            ->removeColumn('hotel')
            ->save();
    }
}
