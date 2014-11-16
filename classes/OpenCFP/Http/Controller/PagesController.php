<?php namespace OpenCFP\Http\Controller;

class PagesController extends BaseController
{
    public function showHomepage()
    {
        return $this->redirectTo('talk_ideas');
        return $this->render('home.twig');
    }

    public function showSpeakerPackage()
    {
        return $this->render('package.twig');
    }

    public function showTalkIdeas()
    {
        return $this->render('ideas.twig');
    }
}
