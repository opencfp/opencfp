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

class RolesMigration extends AbstractMigration
{
    public function up()
    {
        $speakerPermissions  = '{"talk.update":true,"talk.review":false,"user.delete":false}';
        $reviewerPermissions = '{"talk.update":true,"talk.review":true,"user.delete":false}';
        $adminPermissions    = '{"talk.update":true,"talk.review":true,"user.delete":true}';
        $roleData            = [
            ['name' => 'Speaker', 'slug' => 'speaker', 'permissions' => $speakerPermissions],
            ['name' => 'Reviewer', 'slug' => 'reviewer', 'permissions' => $reviewerPermissions],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => $adminPermissions],
        ];
        $this->insert('roles', $roleData);
    }

    public function down()
    {
        $this->table('roles')->truncate();
    }
}
