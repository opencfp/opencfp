<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        $user_mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker_total = $user_mapper->all()->count();

        $talk_mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $favorite_mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\Favorite::class);

        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        
        $recent_talks = $talk_mapper->getRecent($sentry->getUser()->getId());

        $templateData = [
            'speakerTotal' => $speaker_total,
            'talkTotal' => $talk_mapper->all()->count(),
            'favoriteTotal' => $favorite_mapper->all()->count(),
            'selectTotal' => $talk_mapper->all()->where(['selected' => 1])->count(),
            'talks' => $recent_talks,
        ];

        return $this->render('admin/index.twig', $templateData);
    }
}
