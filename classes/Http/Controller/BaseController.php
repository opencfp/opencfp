<?php

namespace OpenCFP\Http\Controller;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use OpenCFP\ContainerAware;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @return RedirectResponse
     */
    public function redirectBack()
    {
        /** @var Request $request */
        $request = $this->service('request_stack')->getCurrentRequest();

        return $this->app->redirect($request->headers->get('referer'));
    }

    public function validate($rules = [], $messages = [], $customAttributes = [])
    {
        /** @var Request $request */
        $request = $this->service('request_stack')->getCurrentRequest();
        $data = $request->query->all() + $request->request->all() + $request->files->all();

        $validation = new Factory(
            new Translator(new FileLoader(new Filesystem(), __DIR__ . '/../../../resources/lang'), 'en'),
            new Container()
        );

        $validator = $validation->make($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw ValidationException::withErrors(array_flatten($validator->errors()->toArray()));
        }

        unset($validation);
        unset($validator);
    }
}
