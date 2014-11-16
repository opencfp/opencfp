<?php namespace OpenCFP\Http\Controller; 

use OpenCFP\Application;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController
{
    /**
     * @var Application
     */
    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function url($route, $parameters = array())
    {
        return $this->app['url_generator']->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function render($name, array $context = [])
    {
        return $this->app['twig']->render($name, $context);
    }

    public function redirectTo($route, $status = 302)
    {
        return $this->app->redirect($this->url($route), $status);
    }
}