<?php

namespace OpenCFP\Test;

use OpenCFP\Application;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Decorates a Symfony Response object and provides several
 * useful assertions for different response types.
 *
 * @mixin Response
 */
class TestResponse
{
    /**
     * @var Application
     */
    private $app;

    /**
     * The response we're decorating.
     *
     * @var Response
     */
    public $baseResponse;

    public function __construct(Application $app, Response $response)
    {
        $this->baseResponse = $response;
        $this->app = $app;
    }

    public function assertSuccessful()
    {
        Assert::assertTrue(
            $this->isSuccessful(),
            "Response status code [{$this->getStatusCode()}] is not a successful status code."
        );

        return $this;
    }

    public function assertStatus($status)
    {
        $actual = $this->getStatusCode();

        Assert::assertTrue(
            $status === $actual,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    public function assertRedirect($route = null, $parameters = [])
    {
        Assert::assertTrue(
            $this->isRedirect(),
            "Response status code [{$this->getStatusCode()}] is not a redirect status code."
        );

        if (!is_null($route)) {
            $expected = $this->app['url_generator']->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
            Assert::assertEquals($expected, $this->headers->get('Location'));
        }

        return $this;
    }

    public function assertSee($content)
    {
        Assert::assertContains($content, $this->getContent());
        return $this;
    }

    public function assertNotSee($content)
    {
        Assert::assertNotContains($content, $this->getContent());
        return $this;
    }

    public function dd()
    {
        dd($this->getContent());
    }

    public function __call($method, $args)
    {
        return $this->baseResponse->{$method}(...$args);
    }
}
