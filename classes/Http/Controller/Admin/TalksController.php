<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkFilter;
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
        $options = [
            'order_by' => $req->get('order_by'),
            'sort' => $req->get('sort'),
        ];

        $pager_formatted_talks = $this->service(TalkFilter::class)->getFilteredTalks(
            $admin_user_id,
            $req->get('filter'),
            $options
        );

        // Set up our page stuff
        $per_page = (int) $req->get('per_page') ?: 20;
        $pagerfanta = new Pagination($pager_formatted_talks, $per_page);
        $pagerfanta->setCurrentPage($req->get('page'));
        $pagination = $pagerfanta->createView('/admin/talks?', $req->query->all());

        $templateData = [
            'pagination' => $pagination,
            'talks' => $pagerfanta->getFanta(),
            'page' => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($pager_formatted_talks),
            'filter' => $req->get('filter'),
            'per_page' => $per_page,
            'sort' => $req->get('sort'),
            'order_by' => $req->get('order_by'),
        ];

        return $this->render('admin/talks/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        $talkId = $req->get('id');
        $talk = Talk::where('id', $talkId)
            ->with(['comments'])
            ->first();

        if (!$talk instanceof Talk) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Could not find requested talk',
            ]);

            return $this->app->redirect($this->url('admin_talks'));
        }

        $userId = $this->service(Authentication::class)->userId();

        // Mark talk as viewed by admin
        $talkMeta = $talk
            ->meta()
            ->firstOrNew([
                'admin_user_id' => $userId,
                'talk_id' => $talkId,
            ]);
        $talkMeta->viewTalk();

        $speaker = $talk->speaker;
        $otherTalks = $speaker->getOtherTalks($talkId);

        // Build and render the template
        $templateData = [
            'talk' => $talk->toArray(),
            'talk_meta' => $talkMeta,
            'speaker' => new SpeakerProfile($speaker),
            'otherTalks' => $otherTalks,
            'comments' => $talk->comments()->get(),
        ];
        return $this->render('admin/talks/view.twig', $templateData);
    }

    public function rateAction(Request $req)
    {
        /** @var TalkRatingStrategy $talkRatingStrategy */
        $talkRatingStrategy = $this->service(TalkRatingStrategy::class);

        try {
            $talk_rating = (int) $req->get('rating');
            $talk_id = (int) $req->get('id');

            $talkRatingStrategy->rate($talk_id, $talk_rating);
        } catch (TalkRatingException $e) {
            return false;
        }

        return true;
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
        $admin_user_id = $this->service(Authentication::class)->userId();
        $talkId = (int) $req->get('id');

        if ($req->get('delete') !== null) {
            // Delete the record that matches
            return Favorite::where('admin_user_id', $admin_user_id)
                ->where('talk_id', $talkId)
                ->first()
                ->delete();
        }

        Favorite::firstOrCreate([
            'admin_user_id' => $admin_user_id,
            'talk_id' => $talkId,
        ]);

        return true;
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
        $talk = Talk::find($req->get('id'));
        if ($talk instanceof Talk) {
            $talk->selected = $req->get('delete') !== null ? 1 :0;
            $talk->save();
            return true;
        }

        return false;
    }

    public function commentCreateAction(Request $req)
    {
        $talk_id = (int)$req->get('id');

        $user = $this->service(Authentication::class)->user();
        $admin_user_id = (int) $user->getId();

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\TalkComment::class);
        $comment = $mapper->get();

        $comment->talk_id = $talk_id;
        $comment->user_id = $admin_user_id;
        $comment->message = $req->get('comment');

        $mapper->save($comment);

        $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => 'Comment Added!',
            ]);

        return $this->app->redirect($this->url('admin_talk_view', ['id' => $talk_id]));
    }
}
