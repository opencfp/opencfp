<?php namespace OpenCFP\Provider;

use OpenCFP\Application;

class ControllerResolver extends \Silex\ControllerResolver
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * We're overriding this protected method to auto-inject the application container
     * into our controllers.
     *
     * @param  string      $controller
     * @return array|mixed
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if ( ! class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        return array(new $class($this->app), $method);
    }
}
