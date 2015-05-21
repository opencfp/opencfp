<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Sessions\SessionInterface as SentrySessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

class SymfonySentrySession implements SentrySessionInterface
{
    private $session;
    private $key;

    public function __construct(SymfonySessionInterface $session, $key = null)
    {
        $this->session = $session;
        $this->key = $key ?: 'cartalyst_sentry';
    }

    public function getKey()
    {
        return $this->key;
    }

    public function put($value)
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
