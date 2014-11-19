<?php

namespace OpenCFP\Http\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{


    public function indexAction(Request $req)
    {
        if (!$this->app['sentry']->check()) {
            return $this->redirectTo('login');
        }

        $user = $this->app['sentry']->getUser();
        $user_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $user_info = $user_mapper->get($user->getId())->toArray();

        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $my_talks = $talk_mapper->getByUser($user->getId());

        // Load our template and RENDER
        $template_data = array(
            'myTalks' => $my_talks,
            'first_name' => $user_info['first_name'],
            'last_name' => $user_info['last_name'],
            'user' => $user_info,
            'company' => $user_info['company'] ?: null,
            'twitter' => $user_info['twitter'],
            'speaker_info' => $user_info['info'],
            'speaker_bio' => $user_info['bio'],
            'transportation' => $user_info['transportation'],
            'hotel' => $user_info['hotel'],
            'speaker_photo' => $user_info['photo_path'],
            'preview_photo' => '/uploads/' . $user_info['photo_path'],
            'airport' => $user_info['airport']
        );

        return $this->render('dashboard.twig', $template_data);
    }
}
