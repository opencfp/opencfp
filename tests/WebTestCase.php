<?php

namespace OpenCFP\Test;

use Mockery;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Validator;
use OpenCFP\Infrastructure\Auth\CsrfValidator;
use OpenCFP\Infrastructure\Auth\UserInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class WebTestCase extends BaseTestCase
{
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

    /**
     * Swap implementations of a service in the container.
     *
     * @param string $service
     * @param object $instance
     *
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
     *
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = \array_merge($this->headers, $headers);

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
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function withNoHeaders()
    {
        $this->headers = [];

        return $this;
    }

    public function withServerVariables(array $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function call(string $method, string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): TestResponse
    {
        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            \array_replace($this->server, $server),
            $content
        );

        $response = $this->app->handle($request);
        $this->app->terminate($request, $response);

        return new TestResponse($this->app, $response);
    }

    public function get(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): TestResponse
    {
        return $this->call('GET', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function post(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): TestResponse
    {
        return $this->call('POST', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function patch(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): TestResponse
    {
        return $this->call('PATCH', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function delete(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): TestResponse
    {
        return $this->call('DELETE', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function callForPapersIsOpen(): self
    {
        $cfp = Mockery::mock(CallForProposal::class);
        $cfp->shouldReceive('isOpen')->andReturn(true);
        $this->swap('callforproposal', $cfp);
        $this->app['twig']->addGlobal('cfp_open', true);

        return $this;
    }

    public function callForPapersIsClosed(): self
    {
        $cfp = Mockery::mock(CallForProposal::class);
        $cfp->shouldReceive('isOpen')->andReturn(false);
        $this->swap('callforproposal', $cfp);
        $this->app['twig']->addGlobal('cfp_open', false);

        return $this;
    }

    public function isOnlineConference(): self
    {
        $config                                     = $this->app['config'];
        $config['application']['online_conference'] = true;
        $this->app['config']                        = $config;
        $this->app['twig']->addGlobal('site', $this->app->config('application'));

        return $this;
    }

    public function asLoggedInSpeaker(int $id = 1): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(false);
        $user->shouldReceive('getLogin')->andReturn('my@email.com');

        // Create a test double for Sentry
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('userId')->andReturn($id);
        $this->swap(Authentication::class, $auth);

        return $this;
    }

    public function asAdmin(int $id = 1): self
    {
        // Set things up so Sentry believes we're logged in
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(false);
        $auth = Mockery::mock(Authentication::class);

        // Create a test double for Sentry
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('userId')->andReturn($id);
        $this->swap(Authentication::class, $auth);

        return $this;
    }

    public function asReviewer(int $id = 1): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(true);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('userId')->andReturn($id);
        $this->swap(Authentication::class, $auth);

        return $this;
    }

    public function passCsrfValidator()
    {
        $csrf= Mockery::mock(Validator::class);
        $csrf->shouldReceive('isValid')->andReturn(true);
        $this->swap(CsrfValidator::class, $csrf);

        return $this;
    }
}
