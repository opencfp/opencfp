<?php
namespace OpenCFP\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Model\Talk;
use OpenCFP\Model\Speaker;
use Pagerfanta\View\TwitterBootstrapView;

class TalksController 
{
    public function indexAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();
        $permissions = $user->getPermissions();

        if (!isset($permissions['admin']) || $permissions['admin'] !== 1) {
            return $app->redirect($app['url'] . '/login');
        }

        $talkModel = new Talk($app['db']);
        $rawTalks = $talkModel->getAll();

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($rawTalks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function($page) {
            return '/admin/talks/' . $page;
        };
        $view = new TwitterBootstrapView();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $template = $app['twig']->loadTemplate('admin/talks.twig');
        $templateData = array(
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage()
        );

        return $template->render($templateData);
    }

    public function viewAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();
        $permissions = $user->getPermissions();

        if (!isset($permissions['admin']) || $permissions['admin'] !== 1) {
            return $app->redirect($app['url'] . '/login');
        }

        // Get info about the talks
        $talkId = $req->get('id');
        $talkModel = new Talk($app['db']);
        $talk = $talkModel->findById($talkId);

        // Get info about our speaker
        $speakerModel = new Speaker($app['db']);
        $speaker = $speakerModel->findByUserId($talk['user_id']);

        $talkUser = $user->find($talk['user_id']);
        $speaker['name'] = "{$talkUser['first_name']} {$talkUser['last_name']}";
        $template = $app['twig']->loadTemplate('admin/view_talk.twig');
        $templateData = array(
            'talk' => $talk,
            'speaker' => $speaker,
            'page' => $req->get('page')
        );
        return $template->render($templateData);
    }
}


