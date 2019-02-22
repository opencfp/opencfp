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

namespace OpenCFP\Http\Action\Page;

use OpenCFP\Domain\Model\Talk;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class HomePageAction
{
    /**
     * @var bool
     */
    private $showSubmissionCount;

    public function __construct(bool $showSubmissionCount)
    {
        $this->showSubmissionCount = $showSubmissionCount;
    }

    /**
     * @Template("home.twig")
     */
    public function __invoke(): array
    {
        return [
            'number_of_talks' => $this->showSubmissionCount ? Talk::count() : '',
        ];
    }
}
