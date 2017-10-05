<?php


use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

class ActivateCurrentUsers extends AbstractMigration
{
    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    public $capsule;

    /** @var \Illuminate\Database\Schema\Builder $capsule */
    public $schema;

    public function bootEloquent()  {
        $adapter = $this->getAdapter()->getAdapter();
        $options = $adapter->getOptions();

        $this->capsule = new Capsule;
        $this->capsule->addConnection([
            'driver'    => 'mysql',
            'database'  => $options['name']
        ]);

        $this->capsule->getConnection()->setPdo($adapter->getConnection());

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();
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
