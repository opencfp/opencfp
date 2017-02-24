<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Http\Form\SignupForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SignupController extends BaseController
{
    use FlashableTrait;

    /**
     * @param Request $req
     * @param \OpenCFP\Application $app
     * @param string $currentTimeString
     * @return mixed|RedirectResponse
     */
    public function indexAction(Request $req, $currentTimeString = 'now')
    {
        if ($this->app['sentinel']->check()) {
            return $this->redirectTo('dashboard');
        }

        $cfp = $this->service('callforproposal');
        $current = new \DateTime($currentTimeString);

        if (!$cfp->isOpen($current)) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, the call for papers has ended.'
                ]);

            return $this->redirectTo('homepage');
        }

        $form = $this->service('form.factory')
            ->createBuilder(SignupForm::class)
            ->getForm();
        return $this->render('user/create.twig', [
            'formAction' => $this->url('user_create'),
            'form' => $form->createView(),
            'buttonInfo' => 'Create my speaker profile',
            'coc_link' => $this->app->config('application.coc_link'),
        ]);
    }

    public function processAction(Request $req)
    {
        $form = $this->service('form.factory')
            ->createBuilder(SignupForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            return $this->render('user/create.twig', [
                'formAction' => $this->url('user_create'),
                'form' => $form->createView(),
                'buttonInfo' => 'Create my speaker profile',
                'coc_link' => $this->app->config('application.coc_link'),
            ]);
        }

        // Form is valid, let's create a user with a default role of 'speaker'
        try {
           $data = $form->getData();
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
