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

namespace OpenCFP\Test\Unit\Http\Action\Forgot;

use OpenCFP\Http\Action\Forgot\IndexAction;
use PHPUnit\Framework;
use Symfony\Component\Form;

final class IndexActionTest extends Framework\TestCase
{
    /**
     * @test
     */
    public function rendersForgotPassword()
    {
        $resetFormView = $this->prophesize(Form\FormView::class);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->createView()
            ->shouldBeCalled()
            ->willReturn($resetFormView);

        $action = new IndexAction($resetForm->reveal());

        $expected = [
            'form'         => $resetFormView->reveal(),
            'current_page' => 'Forgot Password',
        ];

        $this->assertSame($expected, $action());
    }
}
