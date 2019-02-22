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

namespace OpenCFP\Infrastructure\Event;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\Authentication;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;

class TwigGlobalsListener implements EventSubscriberInterface
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(
        Authentication $authentication,
        CallForPapers $callForPapers,
        SessionInterface $session,
        Twig_Environment $twig
    ) {
        $this->authentication = $authentication;
        $this->callForPapers  = $callForPapers;
        $this->twig           = $twig;
        $this->session        = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 128],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        $this->twig->addGlobal('current_page', $request->getRequestUri());
        $this->twig->addGlobal('cfp_open', $this->callForPapers->isOpen());

        // Authentication
        if ($this->authentication->isAuthenticated()) {
            $this->twig->addGlobal('user', $this->authentication->user());
            $this->twig->addGlobal('user_is_admin', $this->authentication->user()->hasAccess('admin'));
            $this->twig->addGlobal('user_is_reviewer', $this->authentication->user()->hasAccess('reviewer'));
        }

        // Flash
        if ($this->session->has('flash')) {
            $this->twig->addGlobal('flash', $this->session->get('flash'));
            $this->session->set('flash', null);
        }
    }
}
