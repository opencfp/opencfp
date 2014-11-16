<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{
    use AdminAccessTrait;

    private function indexAction(Request $req, Application $app)
    {
        $user_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $speaker_total = $user_mapper->all()->count();

        $talk_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $favorite_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');
        $recent_talks = $talk_mapper->getRecent($app['sentry']->getUser()->getId());

        $template = $app['twig']->loadTemplate('admin/index.twig');
        $templateData = array(
            'speakerTotal' => $speaker_total,
            'talkTotal' => $talk_mapper->all()->count(),
            'favoriteTotal' => $favorite_mapper->all()->count(),
            'selectTotal' => $talk_mapper->all()->where(['selected' => 1])->count(),
            'talks' => $recent_talks
        );

        return $template->render($templateData);
    }
}
