<?php

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\Controller\BaseController;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\DefaultView;
use Symfony\Component\HttpFoundation\Request;

class SpeakersController extends BaseController
{
    public function indexAction(Request $req)
    {
        $search = $req->get('search');
        $speakers = User::search($search)->get()->toArray();

        // Set up our page stuff
        $adapter = new ArrayAdapter($speakers);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/reviewer/speakers?page=' . $page;
        };
        $view = new DefaultView();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            ['proximity' => 3]
        );

        $templateData = [
            'pagination' => $pagination,
            'speakers' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'search' => $search ?: '',
        ];

        return $this->render('reviewer/speaker/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        $speakerDetails = User::where('id', $req->get('id'))->first();

        if (!$speakerDetails instanceof User) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Could not find requested speaker',
            ]);

            return $this->app->redirect($this->url('reviewer_speakers'));
        }

        $talks = $speakerDetails->talks()->get()->toArray();
        $templateData = [
            'speaker' => new SpeakerProfile($speakerDetails),
            'talks' => $talks,
            'photo_path' => '/uploads/',
            'page' => $req->get('page'),
        ];

        return $this->render('reviewer/speaker/view.twig', $templateData);
    }
}
