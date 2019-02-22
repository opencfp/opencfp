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

namespace OpenCFP\Test\Unit\Http\Form;

use Localheinz\Test\Util\Helper;
use OpenCFP\Http\Form\FormFactory;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\Form;

final class FormFactoryTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function createCreatesForm()
    {
        $formType = $this->faker()->word;

        $form = $this->prophesize(Form\FormInterface::class);

        $formBuilder = $this->prophesize(Form\FormBuilderInterface::class);

        $formBuilder
            ->getForm()
            ->shouldBeCalled()
            ->willReturn($form);

        $formFactory = $this->prophesize(Form\FormFactoryInterface::class);

        $formFactory
            ->createBuilder(Argument::exact($formType))
            ->shouldBeCalled()
            ->willReturn($formBuilder);

        $this->assertSame($form->reveal(), FormFactory::create($formFactory->reveal(), $formType));
    }
}
