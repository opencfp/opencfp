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

namespace OpenCFP\Test\Unit\Http\Action\Pages;

use Localheinz\Test\Util\Helper;
use OpenCFP\Http\Action\Pages\HomePageAction;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;
use Twig_Environment;

/**
 * @covers \OpenCFP\Http\Action\Pages\HomePageAction
 */
final class HomePageActionTest extends Framework\TestCase
{
    use Helper;

    public function testItReturnsTheCorrectContentIfNoSubmissionCountNeedsToBeShown()
    {
        $content = $this->faker()->text();
        $twig    = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->identicalTo('home.twig'),
                $this->identicalTo(['number_of_talks' => ''])
            )
            ->willReturn($content);
        $action   = new HomePageAction($twig, false);
        $response = $action();
        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertContains($content, $response->getContent());
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @return Framework\MockObject\MockObject|Twig_Environment
     */
    private function createTwigMock(): Twig_Environment
    {
        return $this->createMock(Twig_Environment::class);
    }
}
