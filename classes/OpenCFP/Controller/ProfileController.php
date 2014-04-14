<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Form\SignupForm;
use OpenCFP\Model\Speaker;
use Intervention\Image\Image;

class ProfileController
{
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

    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $template = $app['twig']->loadTemplate('user/edit.twig');
        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile"
            ));
            return $app->redirect($app['url'] . '/dashboard');
        }

        $speaker = new Speaker($app['db']);
        $speaker_data = $speaker->getDetailsByUserId($user->getId());
        $form_data = array(
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'company' => $speaker_data['company'],
            'twitter' => $speaker_data['twitter'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'speaker_photo' => $speaker_data['photo_path'],
            'airport' => $speaker_data['airport'],
            'id' => $user->getId(),
            'formAction' => '/profile/edit',
            'buttonInfo' => 'Update Profile',
            'user' => $user,
        );

        return $template->render($form_data) ;
    }

    public function processAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile"
            ));
            return $app->redirect($app['url'] . '/dashboard');
        }

        $form_data = array(
            'email' => $req->get('email'),
            'user_id' => $req->get('id'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'company' => $req->get('company'),
            'twitter' => $req->get('twitter'),
            'airport' => $req->get('airport'),
            'speaker_info' => $req->get('speaker_info') ?: null,
            'speaker_bio' => $req->get('speaker_bio') ?: null,
        );

        if ($req->files->get('speaker_photo') != null) {
            // Upload Image
            $form_data['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form = new SignupForm($form_data, $app['purifier']);

        $flash = array();
        if ($form->validateAll('update') == true) {
            $sanitized_data = $form->getCleanData();

            // Remove leading @ for twitter
            if ($sanitized_data['twitter'][0] === "@") {
                $sanitized_data['twitter'] = substr($sanitized_data['twitter'], 1);
            }

            if (isset($form_data['speaker_photo'])) {
                // Move file into uploads directory
                $fileName = $form_data['speaker_photo']->getClientOriginalName();
                $form_data['speaker_photo']->move($app['uploadPath'], $fileName);

                // Resize Photo
                $speakerPhoto = Image::make($app['uploadPath'] . '/' . $fileName);

                if ($speakerPhoto->height > $speakerPhoto->width) {
                    $speakerPhoto->resize(250, null, true);
                } else {
                    $speakerPhoto->resize(null, 250, true);
                }

                $speakerPhoto->crop(250, 250);


                // Give photo a unique name
                $sanitized_data['speaker_photo'] = $form_data['first_name'] . '.' . $form_data['last_name'] . uniqid() . '.' . $speakerPhoto->extension;

                // Resize image and destroy original
                if ($speakerPhoto->save($app['uploadPath'] . $sanitized_data['speaker_photo'])) {
                    unlink($app['uploadPath'] . $fileName);
                }
            }

            $speaker = new Speaker($app['db']);
            $response = $speaker->update($sanitized_data);

            if ($response == true) {
                $flash['message'] = "Successfully updated your information!";
                $flash['type'] = 'success';
            }

            if ($response == false) {
                $flash['message'] = "We were unable to update your information. Please try again";
                $flash['type'] = 'error';
            }
        } else {
            $flash['message'] = implode('<br>', $form->getErrorMessages());
            $flash['type'] = 'error';
        }

        $app['session']->set('flash', array(
            'type' => $flash['type'],
            'short' => ucfirst($flash['type']),
            'ext' => $flash['message'],
        ));

        // Update was successful
        if ($response) {
            return $app->redirect($app['url'] . '/profile/edit/' . $form_data['user_id']);
        }

        $form_data['formAction'] = '/profile/edit';
        $form_data['buttonInfo'] = 'Update Profile';
        $form_data['id'] = $user->getId();
        $form_data['user'] = $user;
        $form_data['flash'] = $this->getFlash($app);
        $template = $app['twig']->loadTemplate('user/edit.twig');

        return $template->render($form_data);
    }

    public function passwordAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }
        $user = $app['sentry']->getUser();

        $template = $app['twig']->loadTemplate('user/change_password.twig');

        return $template->render(array('user' => $user));
    }

    public function passwordProcessAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
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
            return $app->redirect($app['url'] . '/profile/change_password');
        }

        $sanitized_data = $form->getCleanData();
        $speaker = new Speaker($app['db']);

        if ($speaker->changePassword($sanitized_data['password'], $user) === false) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Unable to update your password in the database. Please try again."
            ));
            return $app->redirect($app['url'] . '/profile/change_password');
        }

        $app['session']->set('flash', array(
            'type' => 'success',
            'short' => 'Success',
            'ext' => "Changed your password."
        ));

        return $app->redirect($app['url'] . '/profile/change_password');

    }
}

