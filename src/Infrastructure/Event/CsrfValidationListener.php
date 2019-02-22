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

use OpenCFP\Domain\Services\RequestValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CsrfValidationListener implements EventSubscriberInterface
{
    /**
     * @var RequestValidator
     */
    private $csrfValidator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(RequestValidator $csrfValidator, UrlGeneratorInterface $urlGenerator)
    {
        $this->csrfValidator = $csrfValidator;
        $this->urlGenerator  = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            // The router runs on priority 32.
            // We need to make sure, this subscriber runs after the router.
            KernelEvents::REQUEST => ['onKernelRequest', 16],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_require_csrf_token', false)) {
            return;
        }

        if ($this->csrfValidator->isValid($request)) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('dashboard')
        ));
    }
}
