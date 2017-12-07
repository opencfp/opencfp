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

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

/**
 * @covers \OpenCFP\Infrastructure\Event\ExceptionListener
 */
final class ExceptionListenerTest extends WebTestCase
{
    public function testJsonOn404()
    {
        $request = HttpFoundation\Request::create('/invalid/uri');
        $request->headers->set('Accept', 'application/json');

        $response = $this->app->handle($request);

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_NOT_FOUND, $response);
        $this->assertResponseHeader('application/json', 'Content-Type', $response);
        $this->assertResponseBodyJson('{"error": "No route found for \\"GET /invalid/uri\\""}', $response);
    }

    public function testHtmlOn404()
    {
        $response = $this->get('/invalid/uri');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_NOT_FOUND, $response);
        $this->assertResponseBodyContains('Page Not Found!', $response);
    }
}
