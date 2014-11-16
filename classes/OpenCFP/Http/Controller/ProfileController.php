<?php

namespace OpenCFP\Http\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Form\SignupForm;
use Intervention\Image\Image;

class ProfileController extends BaseController
{
    use FlashableTrait;

    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app->url('login'));
        }

        $template = $app['twig']->loadTemplate('user/edit.twig');
        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile"
            ));

            return $app->redirect($app->url('dashboard'));
        }

        $mapper = $app['spot']->mapper('\OpenCFP\Domain\Entity\User');
        $speaker_data = $mapper->get($user->getId())->toArray();

        $form_data = array(
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'company' => $speaker_data['company'],
            'twitter' => $speaker_data['twitter'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'speaker_photo' => $speaker_data['photo_path'],
            'preview_photo' => $app['uploadPath'] . $speaker_data['photo_path'],
            'airport' => $speaker_data['airport'],
            'transportation' => $speaker_data['transportation'],
            'hotel' => $speaker_data['hotel'],
            'id' => $user->getId(),
            'formAction' => $app->url('user_update'),
            'buttonInfo' => 'Update Profile',
        );

        return $template->render($form_data) ;
    }

    public function processAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app->url('login'));
        }

        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile"
            ));

            return $app->redirect($app->url('dashboard'));
        }

        $form_data = array(
            'email' => $req->get('email'),
            'user_id' => $req->get('id'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'company' => $req->get('company'),
            'twitter' => $req->get('twitter'),
            'airport' => $req->get('airport'),
            'transportation' => $req->get('transportation'),
            'hotel' => $req->get('hotel'),
            'speaker_info' => $req->get('speaker_info') ?: null,
            'speaker_bio' => $req->get('speaker_bio') ?: null,
        );

        if ($req->files->get('speaker_photo') != null) {
            // Upload Image
            $form_data['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form = new SignupForm($form_data, $app['purifier']);
        $isValid = $form->validateAll('update');

        if ($isValid) {
            $sanitized_data = $form->getCleanData();

            // Remove leading @ for twitter
            $sanitized_data['twitter'] = preg_replace('/^@/', '', $sanitized_data['twitter']);

            if (isset($form_data['speaker_photo'])) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $form_data['speaker_photo'];
                /** @var \OpenCFP\ProfileImageProcessor $processor */
                $processor = $app['profile_image_processor'];

                $sanitized_data['speaker_photo'] = $form_data['first_name'] . '.' . $form_data['last_name'] . uniqid() . '.' . $file->getClientOriginalExtension();

                $processor->process($file, $sanitized_data['speaker_photo']);
            }

            $mapper = $app['spot']->mapper('\OpenCFP\Domain\Entity\User');
            $user = $mapper->get($user->getId());
            $user->email = $sanitized_data['email'];
            $user->first_name = $sanitized_data['first_name'];
            $user->last_name = $sanitized_data['last_name'];
            $user->company = $sanitized_data['company'];
            $user->twitter = $sanitized_data['twitter'];
            $user->airport = $sanitized_data['airport'];
            $user->transportation = (int) $sanitized_data['transportation'];
            $user->hotel = (int) $sanitized_data['hotel'];
            $user->info = $sanitized_data['speaker_info'];
            $user->bio = $sanitized_data['speaker_bio'];

            if (isset($sanitized_data['speaker_photo'])) {
                $user->photo_path = $sanitized_data['speaker_photo'];
            }

            /** @var $response number of affected rows */
            $response = $mapper->save($user);

            if ($response >= 0) {
                $app['session']->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => "Successfully updated your information!"
                ));

                return $app->redirect($app->url('user_edit', ['id' => $form_data['user_id']]));
            }
        } else {
            $app['session']->set('flash', array(
                    'type' => 'error',
                    'short' => 'Error',
                    'ext' => implode('<br>', $form->getErrorMessages())
                ));
        }

        $form_data['formAction'] = $app->url('user_edit');
        $form_data['buttonInfo'] = 'Update Profile';
        $form_data['id'] = $user->id;
        $form_data['user'] = $user;
        $form_data['flash'] = $this->getFlash($app);
        $template = $app['twig']->loadTemplate('user/edit.twig');

        return $template->render($form_data);
    }

    public function passwordAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app->url('login'));
        }
        $user = $app['sentry']->getUser();

        $template = $app['twig']->loadTemplate('user/change_password.twig');

        return $template->render(array());
    }

    public function passwordProcessAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app->url('login'));
        }

        $user = $app['sentry']->getUser();

        /**
         * Okay, the logic is kind of weird but we can use the SignupFOrm
         * validation code to make sure our password changes are good
         */
        $formData = array(
            'password' => $req->get('password'),
            'password2' => $req->get('password_confirm')
        );
        $form = new SignupForm($formData, $app['purifier']);
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode("<br>", $form->getErrorMessages())
            ));

            return $app->redirect($app->url('password_edit'));
        }

        /**
         * Resetting passwords looks weird because we need to use Sentry's
         * own built-in password reset functionality to do it
         */
        $sanitized_data = $form->getCleanData();
        $reset_code = $user->getResetPasswordCode();

        if (!$user->attemptResetPassword($reset_code, $sanitized_data['password'])) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Unable to update your password in the database. Please try again."
            ));

            return $app->redirect($app->url('password_edit'));
        }

        $app['session']->set('flash', array(
            'type' => 'success',
            'short' => 'Success',
            'ext' => "Changed your password."
        ));

        return $app->redirect($app->url('password_edit'));
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
        $mapper = $app['spot']->mapper('\OpenCFP\Domain\Entity\User');
        $user = $mapper->get($sanitized_data['user_id']);
        $user->email = $sanitized_data['email'];
        $user->first_name = $sanitized_data['first_name'];
        $user->last_name = $sanitized_data['last_name'];
        $user->company = $sanitized_data['company'];
        $user->twitter = $sanitized_data['twitter'];
        $user->airport = $sanitized_data['airport'];
        $user->transportation = $sanitized_data['transportation'];
        $user->hotel = $sanitized_data['hotel'];
        $user->info = $sanitized_data['speaker_info'];
        $user->bio = $sanitized_data['speaker_bio'];

        return $mapper->save($user);
    }
}
