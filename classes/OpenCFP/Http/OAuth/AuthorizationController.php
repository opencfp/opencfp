<?php

namespace OpenCFP\Http\OAuth;

use League\OAuth2\Server\AuthorizationServer;
use OpenCFP\Http\API\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationController extends ApiController
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
     *
     * @return JsonResponse
     */
    public function authorize(Request $request)
    {
        try {
            $authParams = $this->server->getGrantType('authorization_code')->checkAuthorizeParams();
        } catch (\Exception $e) {
            return $this->setStatusCode($e->httpStatusCode)->respond([
                'error' => $e->errorType,
                'message' => $e->getMessage()
            ], $e->getHttpHeaders());
        }

        // Normally at this point you would show the user a sign-in screen and ask them to authorize the requested scopes
        // ...

        // Show the sign-in / register screen.
        // If they have an account, they will sign in and we will have a credential to move forward to authorization.
        // If they do not have an account, they will leave towards the account creation flow and redirect back here to continue authorization.
        // ...
        // If they deny, we redirect back to client application
        // If they approve, we issue authorization code

        // ...
        // Create a new authorize request which will respond with a redirect URI that the user will be redirected to

        $redirectUri = $this->server->getGrantType('authorization_code')->newAuthorizeRequest('user', 1, $authParams);

        return $this->respond([], ['Location' => $redirectUri]);
    }

    /**
     * POST /oauth/access_token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function issueAccessToken(Request $request)
    {
        try {
            $response = $this->server->issueAccessToken();
            return $this->respond($response);
        } catch (\Exception $e) {
            var_dump($e);
            return $this->setStatusCode($e->httpStatusCode)->respond([
                'error' => $e->errorType,
                'message' => $e->getMessage()
            ], $e->getHttpHeaders());
        }
    }
} 