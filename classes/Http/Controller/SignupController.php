<?php

namespace OpenCFP\Http\Controller;

use Cartalyst\Sentry\Users\UserExistsException;
use OpenCFP\Http\Form\SignupForm;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use Silex\Application;
use Spot\Locator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SignupController extends BaseController
{
    use FlashableTrait;

    public function indexAction(Request $req, $currentTimeString = 'now')
    {
        if ($this->app['sentry']->check()) {
            return $this->redirectTo('dashboard');
        }

        if (strtotime($this->app->config('application.enddate') . ' 11:59 PM') < strtotime($currentTimeString)) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, the call for papers has ended.',
            ]);

            return $this->redirectTo('homepage');
        }

        return $this->render('user/create.twig', [
            'transportation' => 0,
            'hotel' => 0,
            'formAction' => $this->url('user_create'),
            'buttonInfo' => 'Create my speaker profile',
        ]);
    }

    public function processAction(Request $req, Application $app)
    {
        $form_data = [
            'formAction' => $this->url('user_create'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'company' => $req->get('company'),
            'twitter' => $req->get('twitter'),
            'email' => $req->get('email'),
            'password' => $req->get('password'),
            'password2' => $req->get('password2'),
            'airport' => $req->get('airport'),
            'buttonInfo' => 'Create my speaker profile',
        ];
        $form_data['speaker_info'] = $req->get('speaker_info') ?: null;
        $form_data['speaker_bio'] = $req->get('speaker_bio') ?: null;
        $form_data['transportation'] = $req->get('transportation') ?: null;
        $form_data['hotel'] = $req->get('hotel') ?: null;
        $form_data['speaker_photo'] = null;

        if ($req->files->get('speaker_photo') !== null) {
            $form_data['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form = new SignupForm($form_data, $app['purifier']);
        $isValid = $form->validateAll();

        if ($isValid) {
            $sanitized_data = $form->getCleanData();

            if (isset($form_data['speaker_photo'])) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $form_data['speaker_photo'];
                /** @var ProfileImageProcessor $processor */
                $processor = $app['profile_image_processor'];
                /** @var PseudoRandomStringGenerator $generator */
                $generator = $app['security.random'];

                /**
                 * The extension technically is not required. We guess the extension using a trusted method.
                 */
                $sanitized_data['speaker_photo'] = $generator->generate(40) . '.' . $file->guessExtension();

                $processor->process($file, $sanitized_data['speaker_photo']);
            }

            // Create account using Sentry
            try {
                $user_data = [
                    'first_name' => $sanitized_data['first_name'],
                    'last_name' => $sanitized_data['last_name'],
                    'company' => $sanitized_data['company'],
                    'twitter' => $sanitized_data['twitter'],
                    'email' => $sanitized_data['email'],
                    'password' => $sanitized_data['password'],
                    'airport' => $sanitized_data['airport'],
                    'activated' => 1,
                ];

                $user = $app['sentry']->getUserProvider()->create($user_data);

                // Add them to the proper group
                $user->addGroup($app['sentry']
                    ->getGroupProvider()
                    ->findByName('Speakers')
                );

                /* @var Locator $spot */
                $spot = $app['spot'];
                
                // Add in the extra speaker information
                $mapper = $spot->mapper('\OpenCFP\Domain\Entity\User');

                $speaker = $mapper->get($user->id);
                $speaker->info = $sanitized_data['speaker_info'];
                $speaker->bio = $sanitized_data['speaker_bio'];
                $speaker->photo_path = $sanitized_data['speaker_photo'];
                $speaker->transportation = (int) $sanitized_data['transportation'];
                $speaker->hotel = (int) $sanitized_data['hotel'];
                $mapper->save($speaker);

                // This is for redirecting to OAuth endpoint if we arrived
                // as part of the Authorization Code Grant flow.
                if ($this->app['session']->has('redirectTo')) {
                    $this->app['sentry']->login($user);

                    return new RedirectResponse($this->app['session']->get('redirectTo'));
                }

                // Set Success Flash Message
                $app['session']->set('flash', [
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => "You've successfully created your account!",
                ]);

                return $this->redirectTo('login');
            } catch (UserExistsException $e) {
                $app['session']->set('flash', [
                    'type' => 'error',
                    'short' => 'Error',
                    'ext' => 'A user already exists with that email address',
                ]);
            }
        }

        if (!$isValid) {
            // Set Error Flash Message
            $app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode("<br>", $form->getErrorMessages()),
            ]);
        }

        $form_data['flash'] = $this->getFlash($app);

        return $this->render('user/create.twig', $form_data);
    }
}
