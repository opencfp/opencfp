<?php

namespace OpenCFP\Test;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use Phinx\Console\Command\Migrate;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class DatabaseTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Capsule
     */
    private $capsule;

    /**
     * @var bool Have we migrated or not?
     */
    private $migrated = false;

    protected function setUp()
    {
        $this->migrate();
        $this->getCapsule()->getConnection()->beginTransaction();
    }

    protected function tearDown()
    {
        $this->capsule->getConnection()->rollBack();
    }

    /**
     * Helper method that wraps interactions with a connection in
     * a transaction. Callable receives default Illuminate Connnection which
     * can be used to work with tables or the schema builder.
     *
     * @param callable $callback
     */
    protected function transaction(callable $callback)
    {
        $this->capsule->getConnection()->beginTransaction();
        $callback($this->capsule->getConnection());
        $this->capsule->getConnection()->rollBack();
    }

    protected function migrate()
    {
        if ($this->migrated) {
            return;
        }

        $input = new ArgvInput(['phinx', 'migrate', '--environment=testing']);
        $output = new NullOutput();

        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $phinx->run($input, $output);

        /** @var Migrate $migrateCommand */
        $migrateCommand = $phinx->get('migrate');
        $adapter = $migrateCommand->getManager()->getEnvironment('testing')->getAdapter()->getAdapter();

        $options = $adapter->getOptions();

        $this->capsule = new Capsule;

        $this->capsule->addConnection([
            'driver'   => 'mysql',
            'database' => $options['name'],
            'host'     => $options['host'],
            'username' => $options['user'],
            'password' => $options['pass']
        ]);

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    protected function getCapsule()
    {
        return $this->capsule;
    }
}
