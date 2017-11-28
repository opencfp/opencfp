<?php

namespace OpenCFP\Http\Controller\Reviewer;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Http\Controller\BaseController;

class DashboardController extends BaseController
{
    public function indexAction()
    {
        $userId        = $this->service(Authentication::class)->userId();
        $talkFormatter = new TalkFormatter();

        /** @var Collection $recent_talks */
        $recent_talks = Talk::recent()->get();
        $recent_talks = $talkFormatter->formatList($recent_talks, $userId);

        $templateData = [
            'speakerTotal'  => User::count(),
            'talkTotal'     => Talk::count(),
            'favoriteTotal' => Favorite::count(),
            'selectTotal'   => Talk::where('selected', 1)->count(),
            'talks'         => $recent_talks,
        ];

        return $this->render('reviewer/index.twig', $templateData);
    }
}
