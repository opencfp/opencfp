<?php

namespace OpenCFP\Infrastructure\Auth;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class CsrfCheck
{
    /**
     * @var CsrfTokenManager
     */
    private $manager;

    public function __construct(CsrfTokenManager $manager)
    {
        $this->manager = $manager;
    }

    public function checkCsrf($tokenId, $token)
    {
        $token = new CsrfToken($tokenId, $token);
        if (!$this->manager->isTokenValid($token)) {
            return new RedirectResponse('/dashboard');
        }
    }
}
