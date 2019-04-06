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
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

/**
 * This migration activates ALL users.
 * If this is not ran all users will be unable to login.
 */
class ActivateUserMigration extends AbstractMigration
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
        /** @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface $activations */
        $activations = Sentinel::getActivationRepository();
        EloquentUser::all()->each(function (EloquentUser $user) use ($activations) {
            $activation = $activations->create($user);
            $activations->complete($user, $activation->getCode());
        });
    }

    public function down()
    {
        $this->bootEloquent();
        /** @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface $activations */
        $activations = Sentinel::getActivationRepository();
        EloquentUser::all()->each(function (EloquentUser $user) use ($activations) {
            $activations->remove($user);
        });
    }
}
