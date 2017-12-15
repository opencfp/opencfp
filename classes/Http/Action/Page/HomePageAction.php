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

use OpenCFP\Domain\Model\Talk;
use Symfony\Component\HttpFoundation;
use Twig_Environment;

final class HomePageAction
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var bool
     */
    private $showSubmissionCount;

    public function __construct(Twig_Environment $twig, bool $showSubmissionCount)
    {
        $this->twig                = $twig;
        $this->showSubmissionCount = $showSubmissionCount;
    }

    public function __invoke(): HttpFoundation\Response
    {
        $content = $this->twig->render('home.twig', [
            'number_of_talks' => $this->showSubmissionCount ? Talk::count() : '',
        ]);

        return new HttpFoundation\Response($content);
    }
}
