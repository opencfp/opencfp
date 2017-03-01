<?php

namespace OpenCFP\Http\Controller;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Http\Form\DataTransformer\EloquentUserToProfileEntityTransformer;
use OpenCFP\Http\Form\Entity\Profile;
use OpenCFP\Http\Form\ProfileForm;
use Silex\Application;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    use FlashableTrait;

    public function editAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if (!$sentinel->check()) {
            return $this->redirectTo('login');
        }

        $user = $sentinel->getUser();

        if ((string) $user->id !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        // Create our form, pass in the Eloquent user returned by Sentinel and then attach the hidden ID field
        $form = $this->service('form.factory')
            ->createBuilder(ProfileForm::class, $user)
            ->addViewTransformer(new EloquentUserToProfileEntityTransformer())
            ->getForm();
        $template_data = [
            'form_path' => $this->url('user_update'),
            'buttonInfo' => 'Update my profile',
            'form' => $form->createView(),
        ];
        return $this->render('user/edit.twig', $template_data) ;
    }

    public function processAction(Request $req)
    {
        $sentinel = $this->service('sentinel');
        $sentinel_user = $sentinel->check();

        if (!$sentinel_user) {
            return $this->redirectTo('login');
        }

        $form = $this->service('form.factory')
            ->createBuilder(ProfileForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            return $this->render('user/create.twig', [
                'form_path' => $this->url('user_update'),
                'form' => $form->createView(),
                'buttonInfo' => 'Update my profile',
            ]);
        }

        $form_user = $form->getData();

        if ((string) $sentinel_user->id !== $form_user->getId()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        if ($form_user->getPhotoPath() !== null) {
            $file = $form_user->getPhotoPath();
            $processor = $this->service('profile_image_processor');
            $generator = $this->service('security.random');
            $filename = $generator->generate(40) . '.' . $file->guessExtension();
            $processor->process($file, $filename);
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $mapper = $spot->mapper('\OpenCFP\Domain\Entity\User');
        $user = $mapper->get($form_user->getId());
        $user->email = $form_user->getEmail();
        $user->first_name = $form_user->getFirstName();
        $user->last_name = $form_user->getLastName();
        $user->company = $form_user->getCompany();
        $user->twitter = $form_user->getTwitter();
        $user->airport = $form_user->getAirport();
        $user->transportation = (int)$form_user->getTransportation();
        $user->hotel = (int)$form_user->getHotel();
        $user->info = $form_user->getInfo();
        $user->bio = $form_user->getBio();

        if ($form_user->getPhotoPath() !== null) {
            $user->photo_path = $filename;
        }

        /** @var $response number of affected rows */
        $response = $mapper->save($user);

        if ($response >= 0) {
            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => "Successfully updated your profile!",
            ]);

            return $this->redirectTo('dashboard');
        } else {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->getErrorMessages()),
            ]);
        }

        return $this->render('user/edit.twig', [
            'form_path' => $this->url('user_update'),
            'buttonInfo' => 'Update my profile',
            'flash' => $this->getFlash($this->app),
            'form' => $form->createView()
        ]);
    }

    public function passwordAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if (!$sentinel->check()) {
            return $this->redirectTo('login');
        }

        return $this->render('user/change_password.twig');
    }

    public function passwordProcessAction(Request $req)
    {
        /* @var Sentry $sentry */
        $sentry = $this->service('sentry');

        if (!$sentry->check()) {
            return $this->redirectTo('login');
        }

        $user = $sentry->getUser();

        /**
         * Okay, the logic is kind of weird but we can use the UserForm
         * validation code to make sure our password changes are good
         */
        $formData = [
            'password' => $req->get('password'),
            'password2' => $req->get('password_confirm'),
        ];
        $form = new UserForm($formData, $this->service('purifier'));
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode("<br>", $form->getErrorMessages()),
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
                'ext' => "Unable to update your password in the database. Please try again.",
            ]);

            return $this->redirectTo('password_edit');
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => "Changed your password.",
        ]);

        return $this->redirectTo('password_edit');
    }
}
