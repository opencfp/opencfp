<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Talk;

use HTMLPurifier;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Form;
use OpenCFP\Http\View;
use OpenCFP\Infrastructure\Templating\Template;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig\Environment;

final class CreateProcessAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var View\TalkHelper
     */
    private $talkHelper;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $applicationEmail;

    /**
     * @var string
     */
    private $applicationTitle;

    /**
     * @var string
     */
    private $applicationEndDate;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        View\TalkHelper $talkHelper,
        CallForPapers $callForPapers,
        HTMLPurifier $purifier,
        Swift_Mailer $mailer,
        string $applicationEmail,
        string $applicationTitle,
        string $applicationEndDate,
        Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication     = $authentication;
        $this->talkHelper         = $talkHelper;
        $this->callForPapers      = $callForPapers;
        $this->purifier           = $purifier;
        $this->mailer             = $mailer;
        $this->applicationEmail   = $applicationEmail;
        $this->applicationTitle   = $applicationTitle;
        $this->applicationEndDate = $applicationEndDate;
        $this->twig               = $twig;
        $this->urlGenerator       = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended',
            ]);

            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $user = $this->authentication->user();

        $form = $this->createTalkForm([
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'type'        => $request->get('type'),
            'level'       => $request->get('level'),
            'category'    => $request->get('category'),
            'desired'     => $request->get('desired'),
            'slides'      => $request->get('slides'),
            'other'       => $request->get('other'),
            'sponsor'     => $request->get('sponsor'),
            'user_id'     => $request->get('user_id'),
        ]);

        $form->sanitize();

        if (!$form->validateAll()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br>', $form->getErrorMessages()),
            ]);

            $content = $this->twig->render('talk/create.twig', [
                'formAction'     => $this->urlGenerator->generate('talk_create'),
                'talkCategories' => $this->talkHelper->getTalkCategories(),
                'talkTypes'      => $this->talkHelper->getTalkTypes(),
                'talkLevels'     => $this->talkHelper->getTalkLevels(),
                'title'          => $request->get('title'),
                'description'    => $request->get('description'),
                'type'           => $request->get('type'),
                'level'          => $request->get('level'),
                'category'       => $request->get('category'),
                'desired'        => $request->get('desired'),
                'slides'         => $request->get('slides'),
                'other'          => $request->get('other'),
                'sponsor'        => $request->get('sponsor'),
                'buttonInfo'     => 'Submit my talk!',
                'flash'          => $request->getSession()->get('flash'),
            ]);

            return new HttpFoundation\Response($content);
        }

        $talk = Model\Talk::create(\array_merge($form->getCleanData(), [
            'user_id' => $user->getId(),
        ]));

        try {
            $this->sendSubmitEmail(
                $user->getLogin(),
                (int) $talk->id
            );
            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => 'Successfully saved talk.',
            ]);
        } catch (\Swift_TransportException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Your talk was saved but we could not send a confirmation email',
            ]);
        }

        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }

    private function createTalkForm(array $data): Form\TalkForm
    {
        return new Form\TalkForm(
            $data,
            $this->purifier,
            [
                'categories' => $this->talkHelper->getTalkCategories(),
                'levels'     => $this->talkHelper->getTalkLevels(),
                'types'      => $this->talkHelper->getTalkTypes(),
            ]
        );
    }

    private function sendSubmitEmail(string $email, int $talkId)
    {
        $talk = Model\Talk::find($talkId, ['title']);

        /** @var Template $template */
        $template = $this->twig->loadTemplate('emails/talk_submit.twig');

        $parameters = [
            'email'   => $this->applicationEmail,
            'title'   => $this->applicationTitle,
            'talk'    => $talk->title,
            'enddate' => $this->applicationEndDate,
        ];

        $message = new Swift_Message();

        $message->setTo($email);
        $message->setFrom(
            $template->renderBlockWithContext('from', $parameters),
            $template->renderBlockWithContext('from_name', $parameters)
        );

        $message->setSubject($template->renderBlockWithContext('subject', $parameters));
        $message->setBody($template->renderBlockWithContext('body_text', $parameters));
        $message->addPart(
            $template->renderBlockWithContext('body_html', $parameters),
            'text/html'
        );

        return $this->mailer->send($message);
    }
}
