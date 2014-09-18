<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController
{
    public function indexAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();
        $user_mapper = $app['spot']->mapper('OpenCFP\Entity\User');
        $user_info = $user_mapper->get($user->getId())->toArray();

        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $my_talks = $talk_mapper->getByUser($user->getId());

        $permissions['admin'] = $user->hasPermission('admin');

        // Load our template and RENDER
        $template = $app['twig']->loadTemplate('dashboard.twig');
        $template_data = array(
            'myTalks' => $my_talks,
            'first_name' => $user_info['first_name'],
            'last_name' => $user_info['last_name'],
            'company' => $user_info['company'] ?: null,
            'twitter' => $user_info['twitter'],
            'speaker_info' => $user_info['info'],
            'speaker_bio' => $user_info['bio'],
            'transportation' => $user_info['transportation'],
            'hotel' => $user_info['hotel'],
            'speaker_photo' => $user_info['photo_path'],
            'preview_photo' => $app['uploadPath'] . $user_info['photo_path'],
            'airport' => $user_info['airport'],
            'permissions' => $permissions,
            'current_page' => '/dashboard'
        );

        return $template->render($template_data);
    }

    public function ideasAction(Request $req, Application $app)
    {
    	// Load our template and RENDER
    	$template = $app['twig']->loadTemplate('ideas.twig');
    	$template_data = array();

    	return $template->render($template_data);
    }

    public function packageAction(Request $req, Application $app)
    {
    	// Load our template and RENDER
    	$template = $app['twig']->loadTemplate('package.twig');
    	$template_data = array();

    	return $template->render($template_data);
    }
}

