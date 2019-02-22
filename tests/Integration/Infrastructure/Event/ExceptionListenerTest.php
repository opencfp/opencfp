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

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use OpenCFP\Environment;
use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

final class ExceptionListenerTest extends WebTestCase
{
    /**
     * @test
     */
    public function jsonOn404()
    {
        $response = $this->get('/invalid/uri', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_NOT_FOUND, $response);
        $this->assertResponseHeader('application/json', 'Content-Type', $response);
        $this->assertResponseBodyJson('{"error": "No route found for \\"GET /invalid/uri\\""}', $response);
    }

    /**
     * @test
     */
    public function htmlOn404()
    {
        $response = $this->get('/invalid/uri');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_NOT_FOUND, $response);
        $this->assertResponseBodyContains('Page Not Found!', $response);
    }

    protected function refreshContainer()
    {
        // Disable debug mode, so we see the rendered error page.
        self::bootKernel(['environment' => Environment::TYPE_TESTING, 'debug' => false]);
        $this->container = self::$kernel->getContainer();
    }
}
