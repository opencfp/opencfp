<?php

namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Services\RequestValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

final class CsrfValidator implements RequestValidator
{
    /**
     * @var CsrfTokenManager
     */
    private $manager;

    public function __construct(CsrfTokenManager $manager)
    {
        $this->manager = $manager;
    }

    public function isValid(Request $request): bool
    {
        $tokenId    = $request->get('token_id');
        $tokenValue = $request->get('token');
        $token      = new CsrfToken($tokenId, $tokenValue);

        return $this->manager->isTokenValid($token);
    }
}
