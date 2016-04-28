<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

abstract class BaseController
{
    use ContainerAware;

    /**
     * Generate an absolute url from a route name.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string the generated URL
     */
    public function url($route, $parameters = [])
    {
        return $this->service('url_generator')->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Returns a rendered Twig response.
     *
     * @param string $name    Twig template name
     * @param array  $context
     * @param int    $status
     *
     * @return mixed
     */
    public function render($name, array $context = [], $status = Response::HTTP_OK)
    {
        /* @var Twig_Environment $twig */
        $twig = $this->service('twig');

        return new Response($twig->render($name, $context), $status);
    }

    /**
     * @param string $route  Route name to redirect to
     * @param int    $status
     *
     * @return RedirectResponse
     */
    public function redirectTo($route, $status = Response::HTTP_FOUND)
    {
        return $this->app->redirect($this->url($route), $status);
    }
}
