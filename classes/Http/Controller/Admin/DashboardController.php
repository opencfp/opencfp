<?php

namespace OpenCFP\Http\Controller\Admin;

use Illuminate\Database\Eloquent\Collection;
use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Http\Controller\BaseController;

class DashboardController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction()
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        $userId = $this->service(Authentication::class)->userId();
        $talkFormatter = new TalkFormatter();

        /** @var Collection $recent_talks */
        $recent_talks = Talk::recent()->get();
        $recent_talks = $talkFormatter->formatList($recent_talks, $userId);

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
