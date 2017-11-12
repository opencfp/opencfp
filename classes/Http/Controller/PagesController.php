<?php

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
