<?php

namespace OpenCFP\Http\OAuth;

use League\OAuth2\Server\AuthorizationServer;
use Symfony\Component\HttpFoundation\Request;

class OAuthController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    /**
     * GET /oauth/authorize
     *
     * @param Request $request
     */
    public function authorize(Request $request)
    {
        // Show the sign-in / register screen.
        // If they have an account, they will sign in and we will have a credential to move forward to authorization.
        // If they do not have an account, they will leave towards the account creation flow and redirect back here to continue authorization.
        // ...
        // If they deny, we redirect back to client application
        // If they approve, we issue authorization code
    }

    /**
     * POST /oauth/access_token
     *
     * @param Request $request
     */
    public function issueAccessToken(Request $request)
    {

    }
} 