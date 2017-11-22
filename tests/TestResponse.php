<?php

namespace OpenCFP\Test;

use OpenCFP\Application;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $this->app          = $app;
    }

    public function assertSuccessful(): self
    {
        Assert::assertTrue(
            $this->isSuccessful(),
            "Response status code [{$this->getStatusCode()}] is not a successful status code."
        );

        return $this;
    }

    public function assertStatus(int $status): self
    {
        $actual = $this->getStatusCode();

        Assert::assertSame(
            $status,
            $actual,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    public function assertRedirect(string $route = null, array $parameters = [])
    {
        Assert::assertTrue(
            $this->isRedirect(),
            "Response status code [{$this->getStatusCode()}] is not a redirect status code."
        );

        if ($route !== null) {
            $expected = $this->app['url_generator']->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
            Assert::assertEquals($expected, $this->headers->get('Location'));
        }

        return $this;
    }

    public function assertSee(string $content): self
    {
        Assert::assertContains($content, $this->getContent());

        return $this;
    }

    public function assertNotSee(string $content): self
    {
        Assert::assertNotContains($content, $this->getContent());

        return $this;
    }

    public function assertFlashContains(string $flash): self
    {
        $fullFlash = $this->app['session']->get('flash');
        $fullFlash = is_array($fullFlash) ? $fullFlash : [];
        Assert::assertContains($flash, $fullFlash);

        return $this;
    }

    public function assertNoFlashSet(): self
    {
        Assert::assertNull($this->app['session']->get('flash'));

        return $this;
    }

    public function assertTargetURLContains(string$targetUrl): self
    {
        Assert::assertInstanceOf(RedirectResponse::class, $this->baseResponse);
        Assert::assertContains($targetUrl, $this->baseResponse->getTargetUrl());

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
