<?php namespace OpenCFP\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $pdo = new \PDO(
            $app->config('database.dsn'),
            $app->config('database.user'),
            $app->config('database.password'),
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $this->checkConnection($pdo);

        $app['db'] = $pdo;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }

    private function checkConnection($pdo)
    {
        $check = $pdo->query('select database() as db')->fetch(\PDO::FETCH_ASSOC);

        if ( ! $check['db']) {
            throw new \Exception('There was a problem connecting to the database. Make
                sure to use proper DSN format. See: http://php.net/manual/en/pdo.connections.php');
        }
    }
}
