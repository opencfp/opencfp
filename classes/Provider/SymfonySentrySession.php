<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Sessions\SessionInterface as SentrySessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

class SymfonySentrySession implements SentrySessionInterface
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
        $this->key     = $key ?: 'cartalyst_sentry';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $value
     */
    public function put($value)
    {
        $this->session->set($this->key, $value);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->session->get($this->key);
    }

    public function forget()
    {
        $this->session->remove($this->key);
    }
}
