<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Http\Form\Entity\User;
use OpenCFP\Http\Form\UserForm;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SignupController extends BaseController
{
    use FlashableTrait;

    /**
     * @param Request $req
     * @param string $currentTimeString
     * @return mixed|RedirectResponse
     */
    public function indexAction(Request $req, $currentTimeString = 'now')
    {
        if ($this->app['sentinel']->check()) {
            return $this->redirectTo('dashboard');
        }

        $current = new \DateTime($currentTimeString);

        if (!$this->service('callforproposal')->isOpen($current)) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, the call for papers has ended.'
                ]);

            return $this->redirectTo('homepage');
        }

        $form = $this->service('form.factory')
            ->createBuilder(UserForm::class, new User)
            ->getForm();

        if ($this->app->config('application.coc_link') !== null) {
            $form->add('agree_coc', CheckboxType::class, [
                'error_bubbling' => true,
                'required' => true,
            ]);
        }
        $form_options = [
            'form_path' => $this->url('user_create'),
            'form' => $form->createView(),
            'buttonInfo' => 'Create my speaker profile',
        ];

        if ($this->app->config('application.coc_link') !== null) {
            $form_options['coc_link'] = $this->app->config('application.coc_link');
        }

        return $this->render('user/create.twig', $form_options);
    }

    public function processAction(Request $req)
    {
        $form = $this->service('form.factory')
            ->createBuilder(UserForm::class)
            ->getForm();

        if ($this->app->config('application.coc_link') !== null) {
            $form->add('agree_coc', CheckboxType::class, [
                'error_bubbling' => true,
                'required' => true,
            ]);
        }

        $form->handleRequest($req);

        if (!$form->isValid()) {
            return $this->render('user/create.twig', [
                'form_path' => $this->url('user_create'),
                'form' => $form->createView(),
                'buttonInfo' => 'Create my speaker profile',
                'coc_link' => $this->app->config('application.coc_link'),
            ]);
        }

        // Form is valid, let's create a user with a default role of 'speaker'
        try {
            // We get back a User entity from which we need to extract the data to create a user
            $user_entity = $form->getData();
            $data = [
                'email' => $user_entity->getEmail(),
                'password' => $user_entity->getPassword(),
                'first_name' => $user_entity->getFirstName(),
                'last_name' => $user_entity->getLastName(),
                'company' => $user_entity->getCompany(),
                'twitter' => $user_entity->getTwitter(),
                'bio' => $user_entity->getBio(),
                'airport' => $user_entity->getAirport(),
                'info' => $user_entity->getInfo(),
                'transportation' => $user_entity->getTransportation(),
                'photo_path' => $user_entity->getPhotoPath()
            ];
            $user = $this->app['sentinel']->registerAndActivate($data);
            $role = $this->app['sentinel']->findRoleBySlug('speaker');
            $role->users()->attach($user);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "There was a problem creating your account, please try again"
            ]);
            return $this->render('user/create.twig', [
                'formAction' => $this->url('user_create'),
                'form' => $form->createView(),
                'buttonInfo' => 'Create my speaker profile',
                'coc_link' => $this->app->config('application.coc_link'),
                'flash' => $this->getFlash($this->app),
            ]);
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => "Your account has been created, you're ready to log in!"
        ]);
        return $this->redirectTo('login');
    }
}
