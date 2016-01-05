<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Http\Controller\FlashableTrait;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SpeakersController extends BaseController
{
    use AdminAccessTrait;
    use FlashableTrait;

    public function indexAction(Request $req)
    {
        $rawSpeakers = $this->app['spot']
            ->mapper(\OpenCFP\Domain\Entity\User::class)
            ->all()
            ->order(['first_name' => 'ASC'])
            ->toArray();

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
        // Check if user is an logged in and an Admin
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('dashboard');
        }

        // Get info about the speaker
        $user_mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker_details = $user_mapper->get($req->get('id'));

        if (empty($speaker_details)) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Could not find requested speaker",
            ]);

            return $this->app->redirect($this->url('admin_speakers'));
        }

        // Get info about the talks
        $talk_mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talks = $talk_mapper->getByUser($req->get('id'))->toArray();

        // Build and render the template
        $templateData = [
            'airport' => $this->app->config('application.airport'),
            'arrival' => date('Y-m-d', $this->app->config('application.arrival')),
            'departure' => date('Y-m-d', $this->app->config('application.departure')),
            'speaker' => $speaker_details,
            'talks' => $talks,
            'photo_path' => '/uploads/',
            'page' => $req->get('page'),
        ];

        return $this->render('admin/speaker/view.twig', $templateData);
    }

    public function deleteAction(Request $req)
    {
        // Check if user is an logged in and an Admin
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('dashboard');
        }

        $mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
        $speaker = $mapper->get($req->get('id'));
        $response = $mapper->delete($speaker);

        $ext = "Successfully deleted the requested user";
        $type = 'success';
        $short = 'Success';

        if ($response === false) {
            $ext = "Unable to delete the requested user";
            $type = 'error';
            $short = 'Error';
        }

        // Set flash message
        $this->app['session']->set('flash', [
            'type' => $type,
            'short' => $short,
            'ext' => $ext,
        ]);

        return $this->redirectTo('admin_speakers');
    }
}
