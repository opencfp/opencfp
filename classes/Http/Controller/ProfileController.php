<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Form\SignupForm;
use Silex\Application;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    use FlashableTrait;

    public function editAction(Request $req)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        if (!$auth->check()) {
            return $this->redirectTo('login');
        }

        $user = $auth->user();

        if ((string) $user->getId() !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper('\OpenCFP\Domain\Entity\User');
        $speaker_data = $mapper->get($user->getId())->toArray();

        $form_data = [
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'company' => $speaker_data['company'],
            'twitter' => $speaker_data['twitter'],
            'url' => $speaker_data['url'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'speaker_photo' => $speaker_data['photo_path'],
            'preview_photo' => '/uploads/' . $speaker_data['photo_path'],
            'airport' => $speaker_data['airport'],
            'transportation' => $speaker_data['transportation'],
            'hotel' => $speaker_data['hotel'],
            'id' => $user->getId(),
            'formAction' => $this->url('user_update'),
            'buttonInfo' => 'Update Profile',
        ];

        return $this->render('user/edit.twig', $form_data) ;
    }

    public function processAction(Request $req)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        if (!$auth->check()) {
            return $this->redirectTo('login');
        }

        $user = $auth->user();

        if ((string) $user->getId() !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $form_data = [
            'email' => $req->get('email'),
            'user_id' => $req->get('id'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'company' => $req->get('company'),
            'twitter' => $req->get('twitter'),
            'url' => $req->get('url'),
            'airport' => $req->get('airport'),
            'transportation' => $req->get('transportation'),
            'hotel' => $req->get('hotel'),
            'speaker_info' => $req->get('speaker_info') ?: null,
            'speaker_bio' => $req->get('speaker_bio') ?: null,
        ];

        if ($req->files->get('speaker_photo') != null) {
            // Upload Image
            $form_data['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form = new SignupForm($form_data, $this->service('purifier'));
        $isValid = $form->validateAll('update');

        if ($isValid) {
            $sanitized_data = $form->getCleanData();

            // Remove leading @ for twitter
            $sanitized_data['twitter'] = preg_replace('/^@/', '', $sanitized_data['twitter']);

            if (isset($form_data['speaker_photo'])) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $form_data['speaker_photo'];
                /** @var \OpenCFP\Domain\Services\ProfileImageProcessor $processor */
                $processor = $this->service('profile_image_processor');
                /** @var PseudoRandomStringGenerator $generator */
                $generator = $this->service('security.random');

                /**
                 * The extension technically is not required. We guess the extension using a trusted method.
                 */
                $sanitized_data['speaker_photo'] = $generator->generate(40) . '.' . $file->guessExtension();

                $processor->process($file, $sanitized_data['speaker_photo']);
            }

            /* @var Locator $spot */
            $spot = $this->service('spot');

            $mapper = $spot->mapper('\OpenCFP\Domain\Entity\User');
            $user = $mapper->get($user->getId());
            $user->email = $sanitized_data['email'];
            $user->first_name = $sanitized_data['first_name'];
            $user->last_name = $sanitized_data['last_name'];
            $user->company = $sanitized_data['company'];
            $user->twitter = $sanitized_data['twitter'];
            $user->url = $sanitized_data['url'];
            $user->airport = $sanitized_data['airport'];
            $user->transportation = (int) $sanitized_data['transportation'];
            $user->hotel = (int) $sanitized_data['hotel'];
            $user->info = $sanitized_data['speaker_info'];
            $user->bio = $sanitized_data['speaker_bio'];

            if (isset($sanitized_data['speaker_photo'])) {
                $user->photo_path = $sanitized_data['speaker_photo'];
            }

            $user->has_made_profile = 1;

            /** @var $response number of affected rows */
            $response = $mapper->save($user);

            if ($response >= 0) {
                $this->service('session')->set('flash', [
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Successfully updated your information!',
                ]);

                return $this->redirectTo('dashboard');
            }
        } else {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->getErrorMessages()),
            ]);
        }

        $form_data['formAction'] = $this->url('user_update');
        $form_data['buttonInfo'] = 'Update Profile';
        $form_data['id'] = $user->id;
        $form_data['user'] = $user;
        $form_data['flash'] = $this->getFlash($this->app);

        return $this->render('user/edit.twig', $form_data);
    }

    public function passwordAction(Request $req)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        if (!$auth->check()) {
            return $this->redirectTo('login');
        }

        return $this->render('user/change_password.twig');
    }

    public function passwordProcessAction(Request $req)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        if (!$auth->check()) {
            return $this->redirectTo('login');
        }

        $user = $auth->user();

        /**
         * Okay, the logic is kind of weird but we can use the SignupForm
         * validation code to make sure our password changes are good
         */
        $formData = [
            'password' => $req->get('password'),
            'password2' => $req->get('password_confirm'),
        ];
        $form = new SignupForm($formData, $this->service('purifier'));
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->getErrorMessages()),
            ]);

            return $this->redirectTo('password_edit');
        }

        /**
         * Resetting passwords looks weird because we need to use Sentry's
         * own built-in password reset functionality to do it
         */
        $sanitized_data = $form->getCleanData();
        $reset_code = $user->getResetPasswordCode();

        if (! $user->attemptResetPassword($reset_code, $sanitized_data['password'])) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Unable to update your password in the database. Please try again.',
            ]);

            return $this->redirectTo('password_edit');
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => 'Changed your password.',
        ]);

        return $this->redirectTo('password_edit');
    }

    /**
     * Method that saves user info using sanitized data and an Entity mapper
     *
     * @param  Application $app
     * @param  array       $sanitized_data
     * @return boolean
     */
    protected function saveUser($app, $sanitized_data)
    {
        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper('\OpenCFP\Domain\Entity\User');
        $user = $mapper->get($sanitized_data['user_id']);
        $user->email = $sanitized_data['email'];
        $user->first_name = $sanitized_data['first_name'];
        $user->last_name = $sanitized_data['last_name'];
        $user->company = $sanitized_data['company'];
        $user->twitter = $sanitized_data['twitter'];
        $user->url = $sanitized_data['url'];
        $user->airport = $sanitized_data['airport'];
        $user->transportation = $sanitized_data['transportation'];
        $user->hotel = $sanitized_data['hotel'];
        $user->info = $sanitized_data['speaker_info'];
        $user->bio = $sanitized_data['speaker_bio'];

        return $mapper->save($user);
    }
}
