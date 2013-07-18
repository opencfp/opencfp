<?php

namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class manages all actions related to talks.
 *
 * @package OpenCFP\Controller
 *
 * @author Chris Hartjes
 * @author Peter Meth
 * @author Hugo Hamon
 */
class TalkController
{
    /**
     * Displays the form to create a new talk.
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        return $app['twig']->render('create_talk.twig', array(
            'form' => $app['cfp']->createTalkForm(),
        ));
    }

    /**
     * Processes the form and saves the new talk to the database.
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $form = $app['cfp']->createTalkForm();
        $form->submit($request->request->get('talk'));

        if ($form->isValid()) {
            $app['cfp']->submitTalkProposal($form);
            $app['session']->getFlashBag()->set('success', 'Succesfully created a talk');
            return $app->redirect('/dashboard');
        }

        return $app['twig']->render('create_talk.twig', array('form' => $form));
    }

    /**
     * Displays the talk edit form.
     *
     * @param Request $request
     * @param Application $app
     * @return string
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function editAction(Request $request, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $id = $request->attributes->getInt('id');
        if (!$data = $app['cfp']->find($id)) {
            $app->abort(404, sprintf("Unable to find current user's talk identified by %u.", $id));
        }

        return $app['twig']->render('edit_talk.twig', array(
            'form' => $app['cfp']->createTalkForm($data),
            'talk' => $data,
        ));
    }

    /**
     * Edits the current logged-in user's talk.
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|string
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function updateAction(Request $request, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $id = $request->attributes->getInt('id');
        if (!$data = $app['cfp']->find($id)) {
            $app->abort(404, sprintf("Unable to find current user's talk identified by %u.", $id));
        }

        $form = $app['cfp']->createTalkForm($data);
        $form->submit($request->request->get('talk'));

        if ($form->isValid()) {
            $app['cfp']->updateTalkProposal($form);
            $app['session']->getFlashBag()->set('success', 'Succesfully updated your talk');
            return $app->redirect('/dashboard');
        }

        return $app['twig']->render('edit_talk.twig', array(
            'form' => $form,
            'talk' => $data,
        ));
    }

    /**
     * Deletes an existing talk submission.
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(Request $request, Application $app)
    {
        if (!$request->isXmlHttpRequest()) {
            $app->abort(404, 'Not an ajax request.');
        }

        if (!$app['sentry']->check()) {
            return $app->json(array('delete' => 'no-user'));
        }

        $deleted = $app['cfp']->cancelTalkProposal($request->request->get('id'));

        return $app->json(array('delete' => $deleted ? 'ok' : 'no'));
    }
}
