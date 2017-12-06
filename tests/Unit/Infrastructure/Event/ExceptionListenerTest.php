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

namespace OpenCFP\Test\Unit\Infrastructure\Event;

use OpenCFP\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \OpenCFP\Infrastructure\Event\ExceptionListener
 */
final class ExceptionListenerTest extends WebTestCase
{
    public function testJsonOn404()
    {
        $request = Request::create('/invalid/uri');
        $request->headers->set('Accept', 'application/json');

        $response = $this->app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('{"error": "No route found for \\"GET /invalid/uri\\""}', $response->getContent());
    }

    public function testHtmlOn404()
    {
        $testResponse = $this->get('/invalid/uri');

        $testResponse->assertStatus(404);
        $testResponse->assertSee('Page Not Found!');
    }
}
