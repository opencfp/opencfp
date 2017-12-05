<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Infrastructure\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

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

        $event->setResponse($this->renderResponse($request, $exception));
    }

    private function renderResponse(Request $request, \Throwable $exception): Response
    {
        if (\in_array('application/json', $request->getAcceptableContentTypes())) {
            $headers = [];

            if ($exception instanceof HttpExceptionInterface) {
                $code    = $exception->getStatusCode();
                $headers = $exception->getHeaders();
            }

            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], $code, $headers);
        }

        $template = 'error/500.twig';

        $templates = [
            Response::HTTP_UNAUTHORIZED => 'error/401.twig',
            Response::HTTP_FORBIDDEN    => 'error/403.twig',
            Response::HTTP_NOT_FOUND    => 'error/404.twig',
        ];

        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if (\array_key_exists($code, $templates)) {
            $template = $templates[$code];
        }

        $message = $this->twig->render($template);

        return new Response($message, $code);
    }
}
