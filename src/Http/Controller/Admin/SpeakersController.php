<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Repository\AirportRepository;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class SpeakersController extends BaseController
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var AccountManagement
     */
    private $accounts;

    /**
     * @var Capsule
     */
    private $capsule;

    /**
     * @var int
     */
    private $applicationArrival;

    /**
     * @var int
     */
    private $applicationDeparture;

    /**
     * @var string
     */
    private $applicationAirport;

    /**
     * @var AirportRepository
     */
    private $airportRepository;

    /**
     * SpeakersController constructor.
     *
     * @param Authentication        $authentication
     * @param AccountManagement     $accounts
     * @param Capsule               $capsule
     * @param Environment           $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $applicationAirport
     * @param int                   $applicationArrival
     * @param int                   $applicationDeparture
     * @param AirportRepository     $airportRepository
     */
    public function __construct(
        Authentication $authentication,
        AccountManagement $accounts,
        Capsule $capsule,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        string $applicationAirport,
        int $applicationArrival,
        int $applicationDeparture,
        AirportRepository $airportRepository
    ) {
        $this->authentication       = $authentication;
        $this->accounts             = $accounts;
        $this->capsule              = $capsule;
        $this->applicationAirport   = $applicationAirport;
        $this->applicationArrival   = $applicationArrival;
        $this->applicationDeparture = $applicationDeparture;
        $this->airportRepository    = $airportRepository;
        parent::__construct($twig, $urlGenerator);
    }

    public function indexAction(Request $request): Response
    {
        $search = $request->query->get('search');

        $adminUsers      = $this->accounts->findByRole('Admin');
        $adminUserIds    = \array_column($adminUsers, 'id');
        $reviewerUsers   = $this->accounts->findByRole('Reviewer');
        $reviewerUserIds = \array_column($reviewerUsers, 'id');

        $rawSpeakers = User::search($search)->get();

        $rawSpeakers = $rawSpeakers->map(function ($speaker) use ($adminUserIds, $reviewerUserIds) {
            if ($speaker['airport']) {
                $airport = $this->airportRepository->withCode($speaker['airport']);

                if ($airport !== null) {
                    $speaker['airport'] = [
                        'code'    => $airport->getCode(),
                        'name'    => $airport->getName(),
                        'country' => $airport->getCountry(),
                    ];
                }
            }

            $speaker['is_admin'] = \in_array($speaker['id'], $adminUserIds);
            $speaker['is_reviewer'] = \in_array($speaker['id'], $reviewerUserIds);
            $speaker['twitterUrl'] = User::twitterUrl($speaker['twitter']);

            return $speaker;
        })->toArray();

        // Set up our page stuff
        $pagerfanta = new Pagination($rawSpeakers);
        $pagerfanta->setCurrentPage($request->query->get('page'));
        $pagination = $pagerfanta->createView('/admin/speakers?');

        return $this->render('admin/speaker/index.twig', [
            'airport'    => $this->applicationAirport,
            'arrival'    => \date('Y-m-d', $this->applicationArrival),
            'departure'  => \date('Y-m-d', $this->applicationDeparture),
            'pagination' => $pagination,
            'speakers'   => $pagerfanta->getFanta(),
            'page'       => $pagerfanta->getCurrentPage(),
            'search'     => $search ?: '',
        ]);
    }

    public function viewAction(Request $request): Response
    {
        $speakerDetails = User::find($request->query->get('id'));

        if (!$speakerDetails instanceof User) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested speaker',
            ]);

            return $this->redirectTo('admin_speakers');
        }

        if ($speakerDetails->airport) {
            $airport = $this->airportRepository->withCode($speakerDetails->airport);

            if ($airport !== null) {
                $speakerDetails->airport = [
                    'code'    => $airport->getCode(),
                    'name'    => $airport->getName(),
                    'country' => $airport->getCountry(),
                ];
            }
        }

        $talks = $speakerDetails->talks()->get();

        // Build and render the template
        return $this->render('admin/speaker/view.twig', [
            'airport'   => $this->applicationAirport,
            'arrival'   => \date('Y-m-d', $this->applicationArrival),
            'departure' => \date('Y-m-d', $this->applicationDeparture),
            'speaker'   => new SpeakerProfile($speakerDetails),
            'talks'     => $talks,
            'page'      => $request->query->get('page'),
        ]);
    }

    public function deleteAction(Request $request): Response
    {
        $this->capsule->getConnection()->beginTransaction();

        try {
            $user = User::findOrFail($request->attributes->get('id'));
            $user->delete();
            $ext   = 'Successfully deleted the requested user';
            $type  = 'success';
            $short = 'Success';
            $this->capsule->getConnection()->commit();
        } catch (\Exception $e) {
            $this->capsule->getConnection()->rollBack();
            $ext   = 'Unable to delete the requested user';
            $type  = 'error';
            $short = 'Error';
        }

        // Set flash message
        $request->getSession()->set('flash', [
            'type'  => $type,
            'short' => $short,
            'ext'   => $ext,
        ]);

        return $this->redirectTo('admin_speakers');
    }

    public function demoteAction(Request $request): Response
    {
        $role = $request->query->get('role');
        $id   = $request->attributes->getInt('id');

        if ($this->authentication->user()->getId() == $id) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Sorry, you cannot remove yourself as ' . $role . '.',
            ]);

            return $this->redirectTo('admin_speakers');
        }

        try {
            $user = $this->accounts->findById($id);
            $this->accounts->demoteFrom($user->getLogin(), $role);

            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => '',
            ]);
        } catch (\Exception $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to remove the ' . $role . '. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_speakers');
    }
}
