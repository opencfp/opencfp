<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;

class SpeakersController extends BaseController
{
    public function indexAction(Request $request)
    {
        $search = $request->get('search');

        /** @var AccountManagement $accounts */
        $accounts        = $this->service(AccountManagement::class);

        $adminUsers      = $accounts->findByRole('Admin');
        $adminUserIds    = \array_column($adminUsers, 'id');
        $reviewerUsers   = $accounts->findByRole('Reviewer');
        $reviewerUserIds = \array_column($reviewerUsers, 'id');

        $rawSpeakers = User::search($search)->get();

        /** @var AirportInformationDatabase $airports */
        $airports = $this->service(AirportInformationDatabase::class);

        $rawSpeakers = $rawSpeakers->map(function ($speaker) use ($airports, $adminUserIds, $reviewerUserIds) {
            try {
                $airport = $airports->withCode($speaker['airport']);

                $speaker['airport'] = [
                    'code'    => $airport->code,
                    'name'    => $airport->name,
                    'country' => $airport->country,
                ];
            } catch (EntityNotFoundException $e) {
                //Do nothing
            }

            $speaker['is_admin'] = \in_array($speaker['id'], $adminUserIds);
            $speaker['is_reviewer'] = \in_array($speaker['id'], $reviewerUserIds);

            return $speaker;
        })->toArray();

        // Set up our page stuff
        $pagerfanta = new Pagination($rawSpeakers);
        $pagerfanta->setCurrentPage($request->get('page'));
        $pagination = $pagerfanta->createView('/admin/speakers?');

        $templateData = [
            'airport'    => $this->app->config('application.airport'),
            'arrival'    => \date('Y-m-d', $this->app->config('application.arrival')),
            'departure'  => \date('Y-m-d', $this->app->config('application.departure')),
            'pagination' => $pagination,
            'speakers'   => $pagerfanta->getFanta(),
            'page'       => $pagerfanta->getCurrentPage(),
            'search'     => $search ?: '',
        ];

        return $this->render('admin/speaker/index.twig', $templateData);
    }

    public function viewAction(Request $request)
    {
        $speakerDetails = User::find($request->get('id'));

        if (!$speakerDetails instanceof User) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested speaker',
            ]);

            return $this->app->redirect($this->url('admin_speakers'));
        }

        /** @var AirportInformationDatabase $airports */
        $airports = $this->service(AirportInformationDatabase::class);

        try {
            $airport = $airports->withCode($speakerDetails->airport);

            $speakerDetails->airport = [
                'code'    => $airport->code,
                'name'    => $airport->name,
                'country' => $airport->country,
            ];
        } catch (EntityNotFoundException $e) {
            //Do nothing
        }

        $talks = $speakerDetails->talks()->get();

        // Build and render the template
        $templateData = [
            'airport'    => $this->app->config('application.airport'),
            'arrival'    => \date('Y-m-d', $this->app->config('application.arrival')),
            'departure'  => \date('Y-m-d', $this->app->config('application.departure')),
            'speaker'    => new SpeakerProfile($speakerDetails),
            'talks'      => $talks,
            'photo_path' => '/uploads/',
            'page'       => $request->get('page'),
        ];

        return $this->render('admin/speaker/view.twig', $templateData);
    }

    public function deleteAction(Request $request)
    {
        /** @var Capsule $capsule */
        $capsule = $this->service(Capsule::class);

        $capsule->getConnection()->beginTransaction();

        try {
            $user = User::findorFail($request->get('id'));
            $user->delete($request->get('id'));
            $ext   = 'Successfully deleted the requested user';
            $type  = 'success';
            $short = 'Success';
            $capsule->getConnection()->commit();
        } catch (\Exception $e) {
            $capsule->getConnection()->rollBack();
            $ext   = 'Unable to delete the requested user';
            $type  = 'error';
            $short = 'Error';
        }

        /** @var Session\Session $session */
        $session = $this->service('session');

        // Set flash message
        $session->set('flash', [
            'type'  => $type,
            'short' => $short,
            'ext'   => $ext,
        ]);

        return $this->redirectTo('admin_speakers');
    }

    public function demoteAction(Request $request)
    {
        /** @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);

        $role     = $request->get('role');
        $id       = (int) $request->get('id');

        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        /** @var Session\Session $session */
        $session = $this->service('session');

        if ($authentication->userId() == $id) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Sorry, you cannot remove yourself as ' . $role . '.',
            ]);

            return $this->redirectTo('admin_speakers');
        }

        try {
            $user = $accounts->findById($id);
            $accounts->demoteFrom($user->getLogin(), $role);

            $session->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => '',
            ]);
        } catch (\Exception $e) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to remove the ' . $role . '. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_speakers');
    }

    public function promoteAction(Request $request)
    {
        /* @var AccountManagement $accounts */
        $accounts = $this->service(AccountManagement::class);

        $role     = $request->get('role');
        $id       = (int) $request->get('id');

        /** @var Session\Session $session */
        $session = $this->service('session');

        try {
            $user = $accounts->findById($id);
            if ($user->hasAccess(\strtolower($role))) {
                $session->set('flash', [
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'User already is in the ' . $role . ' group.',
                ]);

                return $this->redirectTo('admin_speakers');
            }

            $accounts->promoteTo($user->getLogin(), $role);

            $session->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => '',
            ]);
        } catch (\Exception $e) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to promote the ' . $role . '. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_speakers');
    }
}
