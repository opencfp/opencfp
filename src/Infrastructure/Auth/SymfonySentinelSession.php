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

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentinel\Sessions\SessionInterface as SentinelSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

final class SymfonySentinelSession implements SentinelSessionInterface
{
    /**
     * @var SymfonySessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $key;

    public function __construct(SymfonySessionInterface $session, $key = null)
    {
        $this->session = $session;
        $this->key     = $key ?: 'cartalyst_sentinel';
    }

    /**
     * Put a value in the Sentinel session.
     *
     * @param mixed $value
     */
    public function put($value)
    {
        $this->session->set($this->key, $value);
    }

    /**
     * Returns the Sentinel session value.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->session->get($this->key);
    }

    /**
     * Removes the Sentinel session.
     */
    public function forget()
    {
        $this->session->remove($this->key);
    }
}
