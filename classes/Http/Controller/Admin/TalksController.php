<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Http\Controller\FlashableTrait;
use Symfony\Component\HttpFoundation\Request;

class TalksController extends BaseController
{
    use FlashableTrait;

    public function indexAction(Request $req)
    {
        /* @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        $admin_user_id = $auth->userId();
        $options       = [
            'order_by' => $req->get('order_by'),
            'sort'     => $req->get('sort'),
        ];

        $formattedTalks = $this->service(TalkFilter::class)->getTalks(
            $admin_user_id,
            $req->get('filter'),
            $options
        );

        // Set up our page stuff
        $per_page   = (int) $req->get('per_page') ?: 20;
        $pagerfanta = new Pagination($formattedTalks, $per_page);

        $pagerfanta->setCurrentPage($req->get('page'));
        $pagination = $pagerfanta->createView('/admin/talks?', $req->query->all());

        $templateData = [
            'pagination'   => $pagination,
            'talks'        => $pagerfanta->getFanta(),
            'page'         => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($formattedTalks),
            'filter'       => $req->get('filter'),
            'per_page'     => $per_page,
            'sort'         => $req->get('sort'),
            'order_by'     => $req->get('order_by'),
        ];

        return $this->render('admin/talks/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        $talkId = $req->get('id');
        $talk   = Talk::where('id', $talkId)
            ->with(['comments'])
            ->first();

        if (!$talk instanceof Talk) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested talk',
            ]);

            return $this->app->redirect($this->url('admin_talks'));
        }

        $userId = $this->service(Authentication::class)->userId();

        // Mark talk as viewed by admin
        $talkMeta = $talk
            ->meta()
            ->firstOrNew([
                'admin_user_id' => $userId,
                'talk_id'       => $talkId,
            ]);
        $talkMeta->viewTalk();

        $speaker    = $talk->speaker;
        $otherTalks = $speaker->getOtherTalks($talkId);

        // Build and render the template
        $templateData = [
            'talk'       => $talk->toArray(),
            'talk_meta'  => $talkMeta,
            'speaker'    => new SpeakerProfile($speaker),
            'otherTalks' => $otherTalks,
            'comments'   => $talk->comments()->get(),
        ];

        return $this->render('admin/talks/view.twig', $templateData);
    }

    public function rateAction(Request $req)
    {
        return $this->service(TalkHandler::class)
            ->grabTalk((int) $req->get('id'))
            ->rate((int) $req->get('rating'));
    }

    /**
     * Set Favorited Talk [POST]
     *
     * @param Request $req Request Object
     *
     * @return bool
     */
    public function favoriteAction(Request $req)
    {
        return $this->service(TalkHandler::class)
            ->grabTalk((int) $req->get('id'))
            ->setFavorite($req->get('delete') == null);
    }

    /**
     * Set Selected Talk [POST]
     *
     * @param Request $req Request Object
     *
     * @return bool
     */
    public function selectAction(Request $req)
    {
        return $this->service(TalkHandler::class)
            ->grabTalk((int) $req->get('id'))
            ->select($req->get('delete') != true);
    }

    public function commentCreateAction(Request $req)
    {
        $talkId = (int) $req->get('id');

        /** @var TalkHandler $handler */
        $this->service(TalkHandler::class)
            ->grabTalk($talkId)
            ->commentOn($req->get('comment'));

        $this->service('session')->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => 'Comment Added!',
            ]);

        return $this->app->redirect($this->url('admin_talk_view', ['id' => $talkId]));
    }
}
