<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application;
use OpenCFP\Http\Form\ChangePasswordForm;
use OpenCFP\Http\Form\DataTransformer\EloquentUserToProfileEntityTransformer;
use OpenCFP\Http\Form\Entity\ChangePassword;
use OpenCFP\Http\Form\ProfileForm;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    use FlashableTrait;

    /**
     * @param Request $req
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if ($sentinel->check() == false) {
            return $this->redirectTo('login');
        }

        $user = $sentinel->getUser();

        if ((string)$user->id !== (string)$req->get('id')) {
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

    /**
     * @param Request $req
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function processAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if (!$sentinel->check()) {
            return $this->redirectTo('login');
        }

        $sentinel_user = $sentinel->getUser();

        $form = $this->service('form.factory')
            ->createBuilder(ProfileForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            return $this->render('user/edit.twig', [
                'form_path' => $this->url('user_update'),
                'form' => $form->createView(),
                'buttonInfo' => 'Update my profile',
            ]);
        }

        $form_user = $form->getData();

        if ((int) $sentinel_user->id !== (int) $form_user->getId()) {
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
        }

        return $this->render('user/edit.twig', [
            'form_path' => $this->url('user_update'),
            'buttonInfo' => 'Update my profile',
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $req
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function passwordAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if ($sentinel->check() == false) {
            return $this->redirectTo('login');
        }

        $user = $sentinel->getUser();

        // Create a ChangePassword entity for the form to use
        $change_password = new ChangePassword();
        $change_password->setUserId($user->id);
        $form = $this->service('form.factory')
            ->createBuilder(ChangePasswordForm::class, $change_password)
            ->getForm();

        return $this->render('user/change_password.twig', [
            'form' => $form->createView(),
            'form_path' => $this->url('password_change')
        ]);
    }

    /**
     * @param Request $req
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function passwordProcessAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if (!$sentinel->check()) {
            return $this->redirectTo('login');
        }

        $sentinel_user = $sentinel->getUser();

        $form = $this->service('form.factory')
            ->createBuilder(ChangePasswordForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            return $this->render('user/change_password.twig', [
                'form_path' => $this->url('password_change'),
                'form' => $form->createView(),
            ]);
        }

        $change_password = $form->getData();

        if ((string) $sentinel_user->id !== $change_password->getUserId()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot change someone else's password",
            ]);

            return $this->redirectTo('dashboard');
        }

        $updated_user = $sentinel->update($sentinel_user, ['password' => $change_password->getPassword()]);

        if (!$updated_user) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Unable to update your password, please try again"
            ]);

            return $this->redirectTo('dashboard');
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => 'Changed your password.',
        ]);

        return $this->redirectTo('dashboard');
    }

    /**
     * @param Application $app
     */
    public function setApp(Application $app)
    {
        $this->app = $app;
    }
}
