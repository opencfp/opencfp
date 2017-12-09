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

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\Model\Talk;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends BaseController
{
    public function homepageAction(): Response
    {
        return $this->render('home.twig', [
            'number_of_talks' => Talk::count(),
        ]);
    }

    public function speakerPackageAction(): Response
    {
        return $this->render('package.twig');
    }

    public function talkIdeasAction(): Response
    {
        return $this->render('ideas.twig');
    }
}
