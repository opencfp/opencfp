<?php

namespace OpenCFP\Service;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\PasswordRequiredException;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Security\AuthenticationToken;

class AuthenticationService
{
    /**
     * The Sentry instance
     *
     * @var \Cartalyst\Sentry\Sentry
     */
    private $sentry;

    /**
     * Constructor.
     *
     * @param Sentry $sentry The Sentry instance
     */
    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Authenticates the user with his credentials.
     *
     * @param string $user The user's username
     * @param string $password The user's password
     * @return \OpenCFP\Security\AuthenticationTokenInterface
     */
    public function authenticate($user, $password)
    {
        $credentials = array(
            'email'    => $user,
            'password' => $password,
        );

        $token = $this->createAuthenticationToken();
        try {
            $user = $this->sentry->authenticate($credentials, false);
            $token->setUser($user);
            $token->setAuthenticated();
        } catch (LoginRequiredException $e) {
            $token->setAuthenticationError('Missing Email or Password');
        } catch (PasswordRequiredException $e) {
            $token->setAuthenticationError('Missing Email or Password');
        } catch (UserNotFoundException $e) {
            $token->setAuthenticationError('Invalid Email or Password');
        } catch (UserNotActivatedException $e) {
            $token->setAuthenticationError("Your account hasn't been activated. Did you get the activation email?");
        } catch (\Exception $e) {
            // Catch all (generic error)
            $token->setAuthenticationError('Bad credentials');
        }

        return $token;
    }

    /**
     * Creates an authentication token.
     *
     * @return \OpenCFP\Security\AuthenticationTokenInterface
     */
    private function createAuthenticationToken()
    {
        return new AuthenticationToken();
    }

    /**
     * @todo to be removed?
     *
     * @return array
     */
    public function getViewVariables()
    {
        $variables = array();
        if (isset($_REQUEST['email']) && (isset($_REQUEST['passwd']))) {
            if (!$this->authenticate($_REQUEST['email'], $_REQUEST['passwd'])) {
                $variables['errorMessage'] = $this->getAuthenticationMessage();
                $variables['email'] = $_REQUEST['email'];
            } else {
                $variables['redirect'] = '/dashboard';
            }
        }
        return $variables;
    }
}
