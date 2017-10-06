<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Http\Controller\FlashableTrait;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;
use Spot\Locator;
use Spot\Mapper;
use Symfony\Component\HttpFoundation\Request;

class SpeakersController extends BaseController
{
    use AdminAccessTrait;
    use FlashableTrait;

    public function indexAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $rawSpeakers = $spot
            ->mapper(\OpenCFP\Domain\Entity\User::class)
            ->all()
            ->order(['first_name' => 'ASC'])
            ->toArray();

        $airports = $this->service(AirportInformationDatabase::class);

        $rawSpeakers = array_map(function ($speaker) use ($airports) {
            try {
                $airport = $airports->withCode($speaker['airport']);

                $speaker['airport'] = [
                    'code' => $airport->code,
                    'name' => $airport->name,
                    'country' => $airport->country,
                ];
            } catch (\Exception $e) {
                $speaker['airport'] = [
                    'code' => null,
                    'name' => null,
                    'country' => null,
                ];
            }

            return $speaker;
        }, $rawSpeakers);

        /** @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);
        $adminUsers = $accounts->findByRole('Admin');
        $adminUserIds = array_column($adminUsers, 'id');

        foreach ($rawSpeakers as $key => $each) {
            if (in_array($each['id'], $adminUserIds)) {
                $rawSpeakers[$key]['is_admin'] = true;
            } else {
                $rawSpeakers[$key]['is_admin'] = false;
            }
        }

        // Set up our page stuff
        $adapter = new ArrayAdapter($rawSpeakers);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/admin/speakers?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            ['proximity' => 3]
        );

        $templateData = [
            'airport' => $this->app->config('application.airport'),
            'arrival' => date('Y-m-d', $this->app->config('application.arrival')),
            'departure' => date('Y-m-d', $this->app->config('application.departure')),
            'pagination' => $pagination,
            'speakers' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
        ];

        return $this->render('admin/speaker/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        // Get info about the speaker
        $user_mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker_details = $user_mapper->get($req->get('id'));

        if (empty($speaker_details)) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Could not find requested speaker',
            ]);

            return $this->app->redirect($this->url('admin_speakers'));
        }

        $airports = $this->service(AirportInformationDatabase::class);

        try {
            $airport = $airports->withCode($speaker_details->airport);

            $speaker_details->airport = [
                'code' => $airport->code,
                'name' => $airport->name,
                'country' => $airport->country,
            ];
        } catch (\Exception $e) {
            $speaker_details->airport = [
                'code' => null,
                'name' => null,
                'country' => null,
            ];
        }

        // Get info about the talks
        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talks = $talk_mapper->getByUser($req->get('id'))->toArray();

        // Build and render the template
        $templateData = [
            'airport' => $this->app->config('application.airport'),
            'arrival' => date('Y-m-d', $this->app->config('application.arrival')),
            'departure' => date('Y-m-d', $this->app->config('application.departure')),
            'speaker' => new SpeakerProfile($speaker_details),
            'talks' => $talks,
            'photo_path' => '/uploads/',
            'page' => $req->get('page'),
        ];

        return $this->render('admin/speaker/view.twig', $templateData);
    }

    public function deleteAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker = $mapper->get($req->get('id'));

        $connection = $spot->config()->connection();

        $connection->beginTransaction();

        try {
            $this->removeSpeakerTalks($speaker);
            $response = $mapper->delete($speaker);
        } catch (\Exception $e) {
            $response = false;
        }

        if ($response === false) {
            $connection->rollBack();

            $ext = 'Unable to delete the requested user';
            $type = 'error';
            $short = 'Error';
        } else {
            $connection->commit();

            $ext = 'Successfully deleted the requested user';
            $type = 'success';
            $short = 'Success';
        }

        // Set flash message
        $this->service('session')->set('flash', [
            'type' => $type,
            'short' => $short,
            'ext' => $ext,
        ]);

        return $this->redirectTo('admin_speakers');
    }

    /**
     * @param User $speaker
     */
    private function removeSpeakerTalks(User $speaker)
    {
        $spot = $this->service('spot');

        /**
         * @var Mapper $talkMapper
         * @var Mapper $talkCommentMapper
         * @var Mapper $talkMetaMapper
         */
        $talkMapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talkCommentMapper = $spot->mapper(\OpenCFP\Domain\Entity\TalkComment::class);
        $talkMetaMapper = $spot->mapper(\OpenCFP\Domain\Entity\TalkMeta::class);

        $talks = $speaker->talks->execute();

        /** @var \OpenCFP\Domain\Entity\Talk $talk */
        foreach ($talks as $talk) {
            foreach ($talk->comments->execute() as $comment) {
                $talkCommentMapper->delete($comment);
            }

            foreach ($talk->meta->execute() as $meta) {
                $talkMetaMapper->delete($meta);
            }

            $talkMapper->delete($talk);
        }
    }
}
