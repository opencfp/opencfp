<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Http\Form\Login as LoginForm;
use OpenCFP\Http\Form\Entity\Login as LoginEntity;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends BaseController
{
    use FlashableTrait;

    public function indexAction()
    {
        $sentinel = $this->service('sentinel');

        if ($sentinel->check() !== false) {
            return $this->redirectTo('dashboard');
        }

        $form = $this->service('form.factory')
            ->createBuilder(LoginForm::class, new LoginEntity())
            ->getForm();

        return $this->render('login.twig', ['form' => $form->createView()]);
    }

    public function processAction(Request $req, Application $app)
    {
        $form = $this->service('form.factory')
            ->createBuilder(LoginForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid($req)) {
            return $this->redirectTo('login');
        }

        $data = $form->getData();
        $credentials = [
            'email' => $data->getEmail(),
            'password' => $data->getPassword()
        ];
        $sentinel = $this->service('sentinel');
        $user = $sentinel->authenticate($credentials);

        if (!$user) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Invalid email address and/or password"
            ]);
            $template_data['flash'] = $this->getFlash($app);
            $template_data['form'] = $form->createView();
            return $this->render('login.twig', $template_data, Response::HTTP_BAD_REQUEST);
        }

        $sentinel->login($user);

        return $this->redirectTo('dashboard');
    }

    public function outAction()
    {
        // Log the user out and destroy their active session
        $sentinel = $this->service('sentinel');
        $sentinel->logout();
        return $this->redirectTo('homepage');
    }
}
