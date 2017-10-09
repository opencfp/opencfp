<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Controller\BaseController;

class DashboardController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction()
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        $speaker_total = User::all()->count();

        $talkModel = new Talk();
        $favoriteModel = new Favorite();

        $user = $this->service(Authentication::class)->user();
        $recent_talks = $talkModel->getRecent($user->getId());

        $templateData = [
            'speakerTotal' => $speaker_total,
            'talkTotal' => $talkModel->all()->count(),
            'favoriteTotal' => $favoriteModel->all()->count(),
            'selectTotal' => $talkModel->all()->where('selected', 1)->count(),
            'talks' => $recent_talks,
        ];

        return $this->render('admin/index.twig', $templateData);
    }
}
