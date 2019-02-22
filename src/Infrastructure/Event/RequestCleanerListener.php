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

use HTMLPurifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestCleanerListener implements EventSubscriberInterface
{
    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * @param HTMLPurifier $purifier
     */
    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $request->query->replace($this->clean($request->query->all()));
        $request->request->replace($this->clean($request->request->all()));
    }

    private function clean(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $sanitized[$key] = $this->clean($value);

                continue;
            }

            $sanitized[$key] = \preg_replace(
                ['/&amp;/', '/&lt;\b/', '/\b&gt;/'],
                ['&', '<', '>'],
                $this->purifier->purify($value)
            );
        }

        return $sanitized;
    }
}
