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

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

class UserRolesMigration extends AbstractMigration
{
    /** @var Capsule $capsule */
    public $capsule;

    public function bootEloquent()
    {
        $adapter       = $this->getAdapter()->getAdapter();
        $options       = $adapter->getOptions();
        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver'   => 'mysql',
            'database' => $options['name'],
        ]);
        $this->capsule->getConnection()->setPdo($adapter->getConnection());
        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
    }

    public function up()
    {
        $this->bootEloquent();
        $this->doPromotions('Admin');
        $this->doPromotions('Reviewer');
        $this->promoteSpeakers();
    }

    public function down()
    {
        $this->table('role_users')->truncate();
    }

    private function doPromotions($roleName)
    {
        $role = Sentinel::findRoleByName($roleName);

        foreach ($this->getRoleIds($roleName) as $roleId) {
            $role->users()->attach($roleId);
        }
    }

    private function getRoleIds($role)
    {
        try {
            $con       = $this->capsule->getConnection();
            $roleGroup = $con->query()->from('groups')->where('name', $role)->first();
            $roleIds   = $con->query()->from('users_groups')->where('group_id', $roleGroup->id)->get();

            return $roleIds->transform(function ($role) {
                return $role->user_id;
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function promoteSpeakers()
    {
        $roleIds = \array_merge($this->getRoleIds('Admin'), $this->getRoleIds('Reviewer'));
        $users   = \Cartalyst\Sentinel\Users\EloquentUser::all();
        $users   = $users->whereNotIn('id', $roleIds);
        $role    = Sentinel::findRoleByName('Speaker');

        foreach ($users as $user) {
            $role->users()->attach($user->id);
        }
    }
}
