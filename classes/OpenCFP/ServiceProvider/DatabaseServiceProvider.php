<?php

namespace OpenCFP\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * PDO integration into Silex.
 *
 * @author Hugo Hamon <hugo.hamon@sensiolabs.com>
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // Define PDO global configuration parameters
        // These parameters configure the services
        $app['database.dsn']      = null;
        $app['database.user']     = null;
        $app['database.password'] = null;
        $app['database.options']  = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        );

        // Define the PDO services
        $app['db'] = $app->share(function() use ($app) {
            return new \PDO(
                $app['database.dsn'],
                $app['database.user'],
                $app['database.password'],
                $app['database.options']
            );
        });
    }

    public function boot(Application $app)
    {
    }
}
