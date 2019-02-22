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
use Twig_Environment;

final class UpdateAction
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
    private $swiftMailer;

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
     * @var Twig_Environment
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
        Swift_Mailer $swiftMailer,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        string $applicationEmail,
        string $applicationTitle,
        string $applicationEndDate
    ) {
        $this->authentication     = $authentication;
        $this->talkHelper         = $talkHelper;
        $this->callForPapers      = $callForPapers;
        $this->purifier           = $purifier;
        $this->swiftMailer        = $swiftMailer;
        $this->twig               = $twig;
        $this->urlGenerator       = $urlGenerator;
        $this->applicationEmail   = $applicationEmail;
        $this->applicationTitle   = $applicationTitle;
        $this->applicationEndDate = $applicationEndDate;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended',
            ]);

            $url = $this->urlGenerator->generate('talk_view', [
                'id' => $request->get('id'),
            ]);

            return new HttpFoundation\RedirectResponse($url);
        }

        $user = $this->authentication->user();

        $requestData = [
            'id'          => $request->get('id'),
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
        ];

        $form = $this->createTalkForm($requestData);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitizedData = \array_merge($form->getCleanData(), [
                'user_id' => $user->getId(),
            ]);

            /** @var Model\Talk $talk */
            $talk = Model\Talk::find((int) $sanitizedData['id']);

            if ($talk->update($sanitizedData)) {
                $request->getSession()->set('flash', [
                    'type'  => 'success',
                    'short' => 'Success',
                    'ext'   => 'Successfully saved talk.',
                ]);

                $this->sendSubmitEmail(
                    $talk,
                    $user->getLogin()
                );

                $url = $this->urlGenerator->generate('dashboard');

                return new HttpFoundation\RedirectResponse($url);
            }
        }

        $request->getSession()->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);

        $content = $this->twig->render('talk/edit.twig', [
            'formAction'     => $this->urlGenerator->generate('talk_update'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'talkLevels'     => $this->talkHelper->getTalkLevels(),
            'id'             => $request->get('id'),
            'title'          => $request->get('title'),
            'description'    => $request->get('description'),
            'type'           => $request->get('type'),
            'level'          => $request->get('level'),
            'category'       => $request->get('category'),
            'desired'        => $request->get('desired'),
            'slides'         => $request->get('slides'),
            'other'          => $request->get('other'),
            'sponsor'        => $request->get('sponsor'),
            'buttonInfo'     => 'Update my talk!',
            'flash'          => $request->getSession()->get('flash'),
        ]);

        return new HttpFoundation\Response($content);
    }

    private function createTalkForm(array $requestData): Form\TalkForm
    {
        return new Form\TalkForm($requestData, $this->purifier, [
            'categories' => $this->talkHelper->getTalkCategories(),
            'levels'     => $this->talkHelper->getTalkLevels(),
            'types'      => $this->talkHelper->getTalkTypes(),
        ]);
    }

    private function sendSubmitEmail(Model\Talk $talk, string $email): int
    {
        /** @var Template $template */
        $template = $this->twig->loadTemplate('emails/talk_submit.twig');

        $parameters = [
            'email'   => $this->applicationEmail,
            'title'   => $this->applicationTitle,
            'talk'    => $talk->title,
            'enddate' => $this->applicationEndDate,
        ];

        try {
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

            return $this->swiftMailer->send($message);
        } catch (\Exception $e) {
            echo $e;
            die();
        }
    }
}
