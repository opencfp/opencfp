<?php

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class TalksController extends BaseController
{
    public function indexAction(Request $req)
    {
        $reviewerId = $this->service(Authentication::class)->userId();

        $options = [
            'order_by' => $req->get('order_by'),
            'sort'     => $req->get('sort'),
        ];

        $formattedTalks = $this->service(TalkFilter::class)->getTalks(
            $reviewerId,
            $req->get('filter'),
            $options
        );

        $per_page   = (int) $req->get('per_page') ?: 20;
        $pagerfanta = new Pagination($formattedTalks, $per_page);
        $pagerfanta->setCurrentPage($req->get('page'));
        $pagination = $pagerfanta->createView('/reviewer/talks?', $req->query->all());

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

        return $this->render('reviewer/talks/index.twig', $templateData);
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
            'speaker'    => new SpeakerProfile($speaker, $this->app->config('reviewer.users') ?: []),
            'otherTalks' => $otherTalks,
            'comments'   => $talk->comments()->get(),
        ];

        return $this->render('reviewer/talks/view.twig', $templateData);
    }

    public function rateAction(Request $req)
    {
        /** @var TalkRatingStrategy $talkRatingStrategy */
        $talkRatingStrategy = $this->service(TalkRatingStrategy::class);

        try {
            $talk_rating = (int) $req->get('rating');
            $talk_id     = (int) $req->get('id');

            $talkRatingStrategy->rate($talk_id, $talk_rating);
        } catch (TalkRatingException $e) {
            return false;
        }

        return true;
    }
}
