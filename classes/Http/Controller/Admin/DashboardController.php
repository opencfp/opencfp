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

        $user = $this->service(Authentication::class)->user();
        $recent_talks = Talk::recent($user->getId());

        $templateData = [
            'speakerTotal' => User::count(),
            'talkTotal' => Talk::count(),
            'favoriteTotal' => Favorite::count(),
            'selectTotal' => Talk::where('selected', 1)->count(),
            'talks' => $recent_talks,
        ];

        return $this->render('admin/index.twig', $templateData);
    }
}
