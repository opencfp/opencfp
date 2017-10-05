<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Controller\BaseController;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $user_mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker_total = $user_mapper->all()->count();

        $talk_mapper = $this->service('spot')->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $favorite_mapper = $this->service('spot')->mapper(\OpenCFP\Domain\Entity\Favorite::class);

        $user = $this->service(Authentication::class)->user();
        $recent_talks = $talk_mapper->getRecent($user->getId());

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
