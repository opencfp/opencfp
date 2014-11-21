<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Sessions\SessionInterface as SentrySessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

class SymfonySentrySession implements SentrySessionInterface
{
    private $session;
    private $key;

    function __construct(SymfonySessionInterface $session, $key = null)
    {
        $this->session = $session;
        $this->key = $key ?: 'cartalyst_sentry';
    }

    function getKey()
    {
        return $this->key;
    }

    function put($value)
    {
        $this->session->set($this->key, $value);
    }

    public function get()
    {
        return $this->session->get($this->key);
    }

    public function forget()
    {
        $this->session->remove($this->key);
    }
} 