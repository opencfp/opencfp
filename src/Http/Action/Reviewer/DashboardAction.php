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

namespace OpenCFP\Http\Action\Reviewer;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Talk\TalkFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class DashboardAction
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var TalkFormatter
     */
    private $talkFormatter;

    public function __construct(Authentication $authentication, TalkFormatter $talkFormatter)
    {
        $this->authentication = $authentication;
        $this->talkFormatter  = $talkFormatter;
    }

    /**
     * @Template("reviewer/index.twig")
     */
    public function __invoke(): array
    {
        return [
            'speakerTotal'  => Model\Talk::distinct('user_id')->count('user_id'),
            'talkTotal'     => Model\Talk::count(),
            'favoriteTotal' => Model\Favorite::count(),
            'selectTotal'   => Model\Talk::where('selected', 1)->count(),
            'talks'         => $this->talkFormatter->formatList(
                Model\Talk::recent()->get(),
                $this->authentication->user()->getId()
            ),
        ];
    }
}
