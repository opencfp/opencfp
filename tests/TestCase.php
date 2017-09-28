<?php

namespace OpenCFP\Test;

use Cartalyst\Sentry\Users\UserInterface;
use Mockery;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\Request;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Additional headers for a request.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Additional server variables to be sent with a request.
     *
     * @var array
     */
    protected $server = [];

    public function setUp()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }
    }

    protected function tearDown()
    {
        if ($this->app) {
            $this->app->flush();
            $this->app = null;
        }

        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }

    public function createApplication()
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session.test'] = true;
        return $app;
    }

    public function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    /**
     * Swap implementations of a service in the container.
     *
     * @param string $service
     * @param object $instance
     * @return object
     */
    protected function swap($service, $instance)
    {
        $this->app[$service] = $instance;
        return $instance;
    }

    /**
     * Define additional headers to be sent with the request.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Add a header to be sent with the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function withHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withNoHeaders()
    {
        $this->headers = [];
        return $this;
    }

    public function withServerVariables(array $server)
    {
        $this->server = $server;
        return $this;
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $request = Request::create(
            $uri, $method, $parameters,
            $cookies, $files, array_replace($this->server, $server), $content
        );

        $response = $this->app->handle($request);
        $this->app->terminate($request, $response);

        return new TestResponse($this->app, $response);
    }

    public function get($uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        return $this->call('GET', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function post($uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        return $this->call('POST', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function patch($uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        return $this->call('PATCH', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function delete($uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        return $this->call('DELETE', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function callForPapersIsOpen()
    {
        $cfp = Mockery::mock(CallForProposal::class);
        $cfp->shouldReceive('isOpen')->andReturn(true);
        $this->swap('callforproposal', $cfp);
        $this->app['twig']->addGlobal('cfp_open', true);
        return $this;
    }

    public function callForPapersIsClosed()
    {
        $cfp = Mockery::mock(CallForProposal::class);
        $cfp->shouldReceive('isOpen')->andReturn(false);
        $this->swap('callforproposal', $cfp);
        $this->app['twig']->addGlobal('cfp_open', false);
        return $this;
    }

    public function isOnlineConference()
    {
        $config = $this->app['config'];
        $config['application']['online_conference'] = true;
        $this->app['config'] = $config;
        $this->app['twig']->addGlobal('site', $this->app->config('application'));
        return $this;
    }
}