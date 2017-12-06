<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test;

use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decorates a Symfony Response object and provides several
 * useful assertions for different response types.
 *
 * @mixin Response
 */
final class TestResponse
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The response we're decorating.
     *
     * @var Response
     */
    public $baseResponse;

    public function __construct(ContainerInterface $container, Response $response)
    {
        $this->baseResponse = $response;
        $this->container    = $container;
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
            $expected = $this->container->get('url_generator')->generate($route, $parameters);
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
        $fullFlash = $this->container->get('session')->get('flash');
        $fullFlash = \is_array($fullFlash) ? $fullFlash : [];
        Assert::assertContains($flash, $fullFlash);

        return $this;
    }

    public function assertNoFlashSet(): self
    {
        Assert::assertNull($this->container->get('session')->get('flash'));

        return $this;
    }

    public function assertTargetURLContains(string $targetUrl): self
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

    public function __get($name)
    {
        return $this->baseResponse->{$name};
    }
}
