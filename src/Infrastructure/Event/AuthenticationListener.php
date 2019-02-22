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

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\RoleAccess;
use OpenCFP\Infrastructure\Auth\SpeakerAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(Authentication $authentication, UrlGeneratorInterface $urlGenerator)
    {
        $this->authentication = $authentication;
        $this->urlGenerator   = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -1024],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $uri = $event->getRequest()->getRequestUri();

        if (\preg_match('/^\/(talk|profile)/', $uri)) {
            if ($response = SpeakerAccess::userHasAccess($this->authentication)) {
                $event->setResponse($response);
            }

            return;
        }

        if (\preg_match('/^\/admin/', $uri)) {
            if ($response = RoleAccess::userHasAccess($this->authentication, 'admin')) {
                $event->setResponse($response);
            }

            return;
        }

        if (\preg_match('/^\/reviewer/', $uri)) {
            if ($response = RoleAccess::userHasAccess($this->authentication, 'reviewer')) {
                $event->setResponse($response);
            }

            return;
        }
    }
}
