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

namespace OpenCFP\Http\Action\Page;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class TalkIdeasAction
{
    /**
     * @Template("ideas.twig")
     */
    public function __invoke(): array
    {
        return [];
    }
}
