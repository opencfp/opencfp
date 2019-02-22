<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Helper;

use Symfony\Component\HttpFoundation;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait ResponseHelper
{
    final protected function assertResponseIsSuccessful(HttpFoundation\Response $response)
    {
        $this->assertTrue($response->isSuccessful(), 'Failed asserting that the response is successful.');
    }

    final protected function assertResponseIsRedirect(HttpFoundation\Response $response)
    {
        $this->assertTrue($response->isRedirect(), 'Failed asserting that the response is a redirect.');
    }

    final protected function assertResponseStatusCode(int $expected, HttpFoundation\Response $response)
    {
        $actual = $response->getStatusCode();

        $this->assertSame($expected, $actual, \sprintf(
            'Failed asserting that the response status is "%s", got "%s" instead.',
            $expected,
            $actual
        ));
    }

    final protected function assertResponseHeader(string $expected, string $field, HttpFoundation\Response $response)
    {
        $this->assertSame($expected, $response->headers->get($field), \sprintf(
            'Failed asserting that the response header field "%s" equals "%s".',
            $field,
            $expected
        ));
    }

    final protected function assertResponseBodyContains(string $needle, HttpFoundation\Response $response)
    {
        $haystack = $response->getContent();

        $this->assertContains($needle, $haystack, \sprintf(
            'Failed asserting that the response body "%s" contains "%s".',
            $haystack,
            $needle
        ));
    }

    final protected function assertResponseBodyNotContains(string $needle, HttpFoundation\Response $response)
    {
        $haystack = $response->getContent();

        $this->assertNotContains($needle, $haystack, \sprintf(
            'Failed asserting that the response body "%s" does not contain "%s".',
            $haystack,
            $needle
        ));
    }

    final protected function assertResponseBodyEmpty(HttpFoundation\Response $response)
    {
        $actual = $response->getContent();

        $this->assertEmpty($actual, \sprintf(
            'Failed asserting that the response body "%s" is empty.',
            $actual
        ));
    }

    final protected function assertResponseBodySame(string $expected, HttpFoundation\Response $response)
    {
        $actual = $response->getContent();

        $this->assertSame($expected, $actual, \sprintf(
            'Failed asserting that the response body "%s" is "%s".',
            $actual,
            $expected
        ));
    }

    final protected function assertResponseBodyJson(string $expected, HttpFoundation\Response $response)
    {
        $actual = $response->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual, \sprintf(
            'Failed asserting that the response body "%s" equals "%s".',
            $actual,
            $expected
        ));
    }

    protected function assertRedirectResponse(HttpFoundation\Response $response)
    {
        $expectedClass = HttpFoundation\RedirectResponse::class;

        $this->assertInstanceOf($expectedClass, $response, \sprintf(
            'Failed asserting that response is an instance of "%s".',
            $expectedClass
        ));
    }

    final protected function assertRedirectResponseUrlContains(string $expected, HttpFoundation\Response $response)
    {
        $this->assertRedirectResponse($response);

        /** @var HttpFoundation\RedirectResponse $response */
        $actual = $response->getTargetUrl();

        $this->assertContains($expected, $actual, \sprintf(
            'Failed asserting that the redirect URL "%s" equals "%s".',
            $actual,
            $expected
        ));
    }

    final protected function assertRedirectResponseUrlEquals(string $expected, HttpFoundation\Response $response)
    {
        $this->assertRedirectResponse($response);

        /** @var HttpFoundation\RedirectResponse $response */
        $actual = $response->getTargetUrl();

        $this->assertSame($expected, $actual, \sprintf(
            'Failed asserting that the redirect URL "%s" equals "%s".',
            $actual,
            $expected
        ));
    }

    final protected function assertSessionHasNoFlashMessage(HttpFoundation\Session\SessionInterface $session)
    {
        $this->assertNull($session->get('flash'), 'Failed asserting that the session has no flash messages.');
    }

    final protected function assertSessionHasFlashMessage(string $message, HttpFoundation\Session\SessionInterface $session)
    {
        $flash = $session->get('flash');

        $this->assertNotNull($flash, 'Failed asserting that the session has flash messages.');
        $this->assertContains($message, $flash, \sprintf(
            'Failed asserting that the session has a flash message "%s".',
            $message
        ));
    }
}
