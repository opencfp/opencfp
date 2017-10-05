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
     * @var PDO
     */
    private $phinxPdo;

    /**
     * Make sure to call parent::setUp() if you override this.
     */
    protected function setUp()
    {
        $this->migrate();
    }

    protected function migrate()
    {
        $input = new ArgvInput(['phinx', 'migrate', '--environment=memory']);
        $output = new NullOutput();

        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $phinx->run($input, $output);

        /** @var Migrate $migrateCommand */
        $migrateCommand = $phinx->get('migrate');
        $adapter = $migrateCommand->getManager()->getEnvironment('memory')->getAdapter()->getAdapter();

        /** @var PDO $pdo */
        $this->phinxPdo = $adapter->getConnection();
    }

    protected function getCapsule()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
        ]);

        /**
         * Swap PDO instance so that we're using the same in-memory
         * database migrated by Phinx.
         */
        $capsule->getConnection()->setPdo($this->phinxPdo);

        $capsule->setAsGlobal();

        return $capsule;
    }
}
