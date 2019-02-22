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

use OpenCFP\Domain\Services\RequestValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfValidator implements RequestValidator
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $manager;

    public function __construct(CsrfTokenManagerInterface $manager)
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
