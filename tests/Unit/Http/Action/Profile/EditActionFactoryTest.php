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

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Profile\EditAction;
use OpenCFP\Http\Action\Profile\EditActionFactory;
use OpenCFP\PathInterface;

use PHPUnit\Framework;
use Symfony\Component\Routing;

final class EditActionFactoryTest extends Framework\TestCase
{
    use Helper;

    public function testCreateCreatesEditAction()
    {
        $downloadFromPath = $this->faker()->slug();

        $path = $this->prophesize(PathInterface::class);

        $path
            ->downloadFromPath()
            ->shouldBeCalled()
            ->willReturn($downloadFromPath);

        $action = EditActionFactory::create(
            $this->prophesize(Services\Authentication::class)->reveal(),
            $path->reveal(),
            $this->prophesize(\Twig_Environment::class)->reveal(),
            $this->prophesize(Routing\Generator\UrlGeneratorInterface::class)->reveal()
        );

        $this->assertInstanceOf(EditAction::class, $action);
    }
}
