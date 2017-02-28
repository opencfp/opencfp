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
        try {
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
            $user = $sentinel->findByCredentials($credentials);
            $sentinel->login($user);
            return $this->redirectTo('dashboard');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $template_data = [
                'email' => $req->get('email'),
            ];
            $code = Response::HTTP_BAD_REQUEST;
        }
        // Set Success Flash Message
        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => $errorMessage,
        ]);

        $template_data['flash'] = $this->getFlash($app);

        return $this->render('login.twig', $template_data, $code);
    }

    public function outAction()
    {
        /* @var Sentry $sentry */
        $sentry = $this->service('sentry');
        
        $sentry->logout();

        return $this->redirectTo('homepage');
    }
}
