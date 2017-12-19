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

namespace OpenCFP\Http\Action\Profile;

use OpenCFP\Domain\Services;
use OpenCFP\PathInterface;
use Symfony\Component\Routing;
use Twig_Environment;

final class EditActionFactory
{
    public static function create(
        Services\Authentication $authentication,
        PathInterface $path,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ): EditAction {
        return new EditAction(
            $authentication,
            $path->downloadFromPath(),
            $twig,
            $urlGenerator
        );
    }
}
