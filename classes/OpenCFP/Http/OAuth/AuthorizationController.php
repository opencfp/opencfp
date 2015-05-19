<?php

namespace OpenCFP\Http\OAuth;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Util\RedirectUri;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Http\API\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationController extends ApiController
{

    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var IdentityProvider
     */
    private $identityProvider;

    /**
     * @param AuthorizationServer $server
     * @param IdentityProvider    $identityProvider
     */
    public function __construct(AuthorizationServer $server, IdentityProvider $identityProvider)
    {
        $this->server = $server;
        $this->identityProvider = $identityProvider;
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

            $this->service('session')->set('authParams', $authParams);
            $this->service('session')->set('redirectTo', $request->getUri());

            // Grab currently authenticated user, if authenticated.
            $this->identityProvider->getCurrentUser();

            // Show authorization interface
            return $this->service('twig')->render('oauth/authorize.twig', ['authParams' => $authParams]);
        } catch (NotAuthenticatedException $e) {
            // Authenticate user and come back here.
            return $this->redirectTo('login');
        } catch (OAuthException $e) {
            return $this->setStatusCode($e->httpStatusCode)->respond([
                'error' => $e->errorType,
                'message' => $e->getMessage()
            ], $e->getHttpHeaders());
        }
    }

    public function issueAuthCode(Request $request)
    {
        $authParams = $this->service('session')->get('authParams');

        if ($request->get('authorization') === 'Approve') {
            $user = $this->identityProvider->getCurrentUser();

            $redirectUri = $this->server->getGrantType('authorization_code')
                ->newAuthorizeRequest('user', $user->id, $authParams);

            $this->service('session')->remove('authParams');

            return $this->setStatusCode(302)->respond('', ['Location' => $redirectUri]);
        } else {
            $error = new AccessDeniedException;

            $redirectUri = RedirectUri::make($authParams['redirect_uri'], [
                'error' => $error->errorType,
                'message' => $error->getMessage()
            ]);

            return $this->setStatusCode(302)->respond('', ['Location' => $redirectUri]);
        }
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
            return $this->setStatusCode($e->httpStatusCode)->respond([
                'error' => $e->errorType,
                'message' => $e->getMessage()
            ], $e->getHttpHeaders());
        }
    }
} 