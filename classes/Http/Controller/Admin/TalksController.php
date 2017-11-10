<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Entity\Talk as TalkEntity;
use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Http\Controller\FlashableTrait;
use Spot\Locator;
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

        $pager_formatted_talks = $this->getFilteredTalks(
            $req->get('filter'),
            $admin_user_id,
            $options
        );

        $per_page = (int) $req->get('per_page') ?: 20;

        // Set up our page stuff
        $pagerfanta = new Pagination($pager_formatted_talks, $per_page);

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        $queryParams = $req->query->all();
        // Create our default view for the navigation options
        $routeGenerator = function ($page) use ($queryParams) {
            $queryParams['page'] = $page;
            return '/admin/talks?' . http_build_query($queryParams);
        };
        $pagination = $pagerfanta->createView($routeGenerator);

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

    private function getFilteredTalks($filter = null, $admin_user_id, $options = [])
    {
        /* @var Locator $spot */
        $spot = $this->service('spot');

        /** @var \OpenCFP\Domain\Entity\Mapper\Talk $talk_mapper */
        $talk_mapper = $spot->mapper(TalkEntity::class);
        if ($filter === null) {
            return $talk_mapper->getAllPagerFormatted($admin_user_id, $options);
        }

        switch (strtolower($filter)) {
            case 'selected':
                return $talk_mapper->getSelected($admin_user_id, $options);
                break;

            case 'notviewed':
                return $talk_mapper->getNotViewedByUserId($admin_user_id, $options);
                break;

            case 'notrated':
                return $talk_mapper->getNotRatedByUserId($admin_user_id, $options);
                break;

            case 'toprated':
                return $talk_mapper->getTopRatedByUserId($admin_user_id, $options);
                break;

            case 'plusone':
                return $talk_mapper->getPlusOneByUserId($admin_user_id, $options);
                break;

            case 'viewed':
                return $talk_mapper->getViewedByUserId($admin_user_id, $options);
                break;

            case 'favorited':
                return $talk_mapper->getFavoritesByUserId($admin_user_id, $options);
                break;

            default:
                return $talk_mapper->getAllPagerFormatted($admin_user_id, $options);
        }
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
     * @param  Request $req Request Object
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
     * @param  Request $req Request Object
     *
     * @return bool
     */
    public function selectAction(Request $req)
    {
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper(TalkEntity::class);
        $talk = $mapper->get($req->get('id'));

        $selected = 1;

        if ($status == false) {
            $selected = 0;
        }

        $talk->selected = $selected;
        $mapper->save($talk);

        return true;
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
