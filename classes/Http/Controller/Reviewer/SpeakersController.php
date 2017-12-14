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

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class SpeakersController extends BaseController
{
    /**
     * @var array
     */
    private $reviewerUsers;

    public function __construct(Twig_Environment $twig, UrlGeneratorInterface $urlGenerator, array $reviewerUsers)
    {
        $this->reviewerUsers = $reviewerUsers;

        parent::__construct($twig, $urlGenerator);
    }

    public function indexAction(Request $request): Response
    {
        $search   = $request->get('search');
        $speakers = User::search($search)->get()->toArray();
        // Set up our page stuff
        $pagerfanta = new Pagination($speakers);
        $pagerfanta->setCurrentPage($request->get('page'));
        $pagination = $pagerfanta->createView('/reviewer/speakers?');

        return $this->render('reviewer/speaker/index.twig', [
            'pagination' => $pagination,
            'speakers'   => $pagerfanta->getFanta(),
            'page'       => $pagerfanta->getCurrentPage(),
            'search'     => $search ?: '',
        ]);
    }

    public function viewAction(Request $request): Response
    {
        $speakerDetails = User::where('id', $request->get('id'))->first();

        if (!$speakerDetails instanceof User) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested speaker',
            ]);

            return $this->redirectTo('reviewer_speakers');
        }

        $talks = $speakerDetails->talks()->get()->toArray();

        return $this->render('reviewer/speaker/view.twig', [
            'speaker'    => new SpeakerProfile($speakerDetails, $this->reviewerUsers),
            'talks'      => $talks,
            //TODO: use Path downloadFrom function function
            'photo_path' => '/uploads/',
            'page'       => $request->get('page'),
        ]);
    }
}
