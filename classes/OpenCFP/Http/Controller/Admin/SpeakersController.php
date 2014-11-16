<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use OpenCFP\Model\Talk;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;
use OpenCFP\Controller\FlashableTrait;

class SpeakersController extends BaseController
{
    use AdminAccessTrait;
    use FlashableTrait;

    public function getFlash(Application $app)
    {
        $flash = $app['session']->get('flash');
        $this->clearFlash($app);

        return $flash;
    }

    public function clearFlash(Application $app)
    {
        $app['session']->set('flash', null);
    }

    private function indexAction(Request $req, Application $app)
    {
        $rawSpeakers = $app['spot']
            ->mapper('\OpenCFP\Entity\User')
            ->all()
            ->order(['last_name' => 'ASC'])
            ->toArray();

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($rawSpeakers);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
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
            array('proximity' => 3)
        );

        $template = $app['twig']->loadTemplate('admin/speaker/index.twig');
        $templateData = array(
            'airport' => $app['confAirport'],
            'arrival' => $app['arrival'],
            'departure' => $app['departure'],
            'pagination' => $pagination,
            'speakers' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage()
        );

        return $template->render($templateData);
    }

    private function viewAction(Request $req, Application $app)
    {
        // Check if user is an logged in and an Admin
        if (!$this->userHasAccess($app)) {
            return $app->redirect($app->url('dashboard'));
        }

        // Get info about the speaker
        $user_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $speaker_details = $user_mapper->get($req->get('id'))->toArray();

        // Get info about the talks
        $talk_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $talk_mapper->getByUser($req->get('id'))->toArray();

        // Build and render the template
        $template = $app['twig']->loadTemplate('admin/speaker/view.twig');
        $templateData = array(
            'airport' => $app['confAirport'],
            'arrival' => $app['arrival'],
            'departure' => $app['departure'],
            'speaker' => $speaker_details,
            'talks' => $talks,
            'photo_path' => $app['uploadPath'],
            'page' => $req->get('page'),
        );

        return $template->render($templateData);
    }

    private function deleteAction(Request $req, Application $app)
    {
        // Check if user is an logged in and an Admin
        if (!$this->userHasAccess($app)) {
            return $app->redirect($app->url('dashboard'));
        }

        $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
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
        $app['session']->set('flash', array(
            'type' => $type,
            'short' => $short,
            'ext' => $ext
        ));

        return $app->redirect($app->url('admin_speakers'));
    }

}
