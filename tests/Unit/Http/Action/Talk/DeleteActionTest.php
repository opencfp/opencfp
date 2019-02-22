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

namespace OpenCFP\Test\Unit\Http\Action\Talk;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Http\Action\Talk\DeleteAction;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class DeleteActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function respondsWithNoIfCallForPapersIsClosed()
    {
        $request = $this->createRequestMock();

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->never())
            ->method($this->anything());

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $action = new DeleteAction(
            $authentication,
            $callForPapers
        );

        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\JsonResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());

        $expectedContent = \json_encode([
            'delete' => 'no',
        ]);

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * @deprecated
     *
     * @return CallForPapers|Framework\MockObject\MockObject
     */
    private function createCallForPapersMock(): CallForPapers
    {
        return $this->createMock(CallForPapers::class);
    }
}
