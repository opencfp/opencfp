<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Entity\User as UserEntity;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Http\Controller\FlashableTrait;
use Spot\Locator;
use Spot\Mapper;
use Symfony\Component\HttpFoundation\Request;

class SpeakersController extends BaseController
{
    use FlashableTrait;

    public function indexAction(Request $req)
    {
        $search = $req->get('search');

        /** @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);
        $adminUsers = $accounts->findByRole('Admin');
        $adminUserIds = array_column($adminUsers, 'id');

        $rawSpeakers = User::search($search)->get();
        $airports = $this->service(AirportInformationDatabase::class);
        $rawSpeakers = $rawSpeakers->map(function ($speaker) use ($airports, $adminUserIds) {
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

            $speaker['is_admin'] = in_array($speaker['id'], $adminUserIds);
            return $speaker;
        })->toArray();

        // Set up our page stuff
        $pagerfanta = new Pagination($rawSpeakers);
        $pagerfanta->setCurrentPage($req->get('page'));
        $pagination = $pagerfanta->createView('/admin/speakers?');

        $templateData = [
            'airport' => $this->app->config('application.airport'),
            'arrival' => date('Y-m-d', $this->app->config('application.arrival')),
            'departure' => date('Y-m-d', $this->app->config('application.departure')),
            'pagination' => $pagination,
            'speakers' => $pagerfanta->getFanta(),
            'page' => $pagerfanta->getCurrentPage(),
            'search' => $search ?: '',
        ];

        return $this->render('admin/speaker/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        $speaker_details = User::find($req->get('id'));

        if (!$speaker_details instanceof User) {
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

        $talks = $speaker_details->talks()->get();

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
     * @param UserEntity $speaker
     */
    private function removeSpeakerTalks(UserEntity $speaker)
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

    public function demoteAction(Request $req)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        /** @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);

        $admin = $auth->user();

        if ($admin->getId() == $req->get('id')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, you cannot remove yourself as Admin.',
            ]);

            return $this->redirectTo('admin_speakers');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $user_data = $mapper->get($req->get('id'))->toArray();
        $user = $accounts->findByLogin($user_data['email']);

        try {
            $accounts->demoteFrom($user->getLogin());

            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => '',
            ]);
        } catch (\Exception $e) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'We were unable to remove the Admin. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_speakers');
    }

    public function promoteAction(Request $req)
    {
        /* @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $user_data = $mapper->get($req->get('id'))->toArray();
        $user = $accounts->findByLogin($user_data['email']);

        if ($user->hasAccess('admin')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'User already is in the Admin group.',
            ]);

            return $this->redirectTo('admin_speakers');
        }

        try {
            $accounts->promoteTo($user->getLogin());

            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => '',
            ]);
        } catch (\Exception $e) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'We were unable to promote the Admin. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_speakers');
    }
}
