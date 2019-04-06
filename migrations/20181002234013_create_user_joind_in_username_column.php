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

use Cartalyst\Sentinel\Users\EloquentUser;
use Phinx\Migration\AbstractMigration;

class CreateUserJoindInUsernameColumn extends AbstractMigration
{
    /** @var Capsule $capsule */
    public $capsule;

    public function bootEloquent(): void
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

    public function up(): void
    {
        // Create joindin_username
        $this->table('users')
            ->addColumn('joindin_username', 'string', ['null' => true])
            ->update();

        // Go through each record in user, strip out (https://joind.in/user/) and copy to joindin_username
        $joindInRegex = '/^https:\/\/joind\.in\/user\/(.{1,100})$/';

        $users = EloquentUser::all();

        foreach ($users as $user) {
            if (\preg_match($joindInRegex, $user->url, $matches) === 1) {
                $user->joindin_username = $matches[1];
                $user->url              = null;
                $user->save();
            }
        }
    }

    public function down(): void
    {
        // Go through each record in user, update `url` to move the joindin_username to there
        $users = EloquentUser::all();

        foreach ($users as $user) {
            $user->url = $user->joindin_username
                                        ? 'https://joind.in/user/' . $user->joindin_username
                                        : null;
            $user->joindin_username = null;
            $user->save();
        }
        // Drop the joindin_username column
        $this->table('users')
            ->removeColumn('joindin_username')
            ->update();
    }
}
