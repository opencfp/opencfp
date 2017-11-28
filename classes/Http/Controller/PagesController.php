<?php

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

class PagesController extends BaseController
{
    public function showHomepage()
    {
        return $this->render('home.twig', $this->getContextWithTalksCount());
    }

    public function showSpeakerPackage()
    {
        return $this->render('package.twig', $this->getContextWithTalksCount());
    }

    public function showTalkIdeas()
    {
        return $this->render('ideas.twig', $this->getContextWithTalksCount());
    }

    private function getContextWithTalksCount()
    {
        return ['number_of_talks' => Talk::count()];
    }
}
