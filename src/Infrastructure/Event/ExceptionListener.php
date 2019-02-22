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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -8],
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if (!\in_array('application/json', $request->getAcceptableContentTypes())) {
            return;
        }

        $event->setResponse($this->renderResponse($exception));
    }

    private function renderResponse(\Throwable $exception): Response
    {
        $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = [];

        if ($exception instanceof HttpExceptionInterface) {
            $code    = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        }

        return new JsonResponse(
            ['error' => $exception->getMessage()],
            $code,
            $headers
        );
    }
}
