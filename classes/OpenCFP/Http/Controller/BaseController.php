<?php namespace OpenCFP\Http\Controller; 

use OpenCFP\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * Generate an absolute url from a route name.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string the generated URL
     */
    public function url($route, $parameters = array())
    {
        return $this->app['url_generator']->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Returns a rendered Twig response.
     *
     * @param string $name Twig template name
     * @param array $context
     *
     * @return mixed
     */
    public function render($name, array $context = [])
    {
        return $this->app['twig']->render($name, $context);
    }

    /**
     * @param string $route Route name to redirect to
     * @param int $status
     *
     * @return RedirectResponse
     */
    public function redirectTo($route, $status = 302)
    {
        return $this->app->redirect($this->url($route), $status);
    }
}