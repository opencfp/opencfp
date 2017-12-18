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

namespace OpenCFP\Test\Unit\Http\Action\Profile;

use OpenCFP\Http\Action\Profile\PasswordAction;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use Symfony\Component\HttpFoundation;

final class PasswordActionTest extends AbstractActionTestCase
{
    public function testRendersPasswordChange()
    {
        $content = $this->faker()->text();

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with($this->identicalTo('user/change_password.twig'))
            ->willReturn($content);

        $action = new PasswordAction($twig);

        $response = $action();

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }
}
