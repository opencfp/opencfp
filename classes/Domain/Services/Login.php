<?php

namespace OpenCFP\Domain\Services;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Users\UserNotFoundException;

class Login
{
    /**
     * @var Sentry
     */
    private $sentry;

    /**
     * @var string
     */
    private $authenticationMessage = '';

    /**
     * @param Sentry $sentry
     */
    public function __construct($sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * @param string $user
     * @param string $password
     *
     * @return bool
     */
    public function authenticate($user, $password)
    {
        if (empty($user) || empty($password)) {
            $this->authenticationMessage = "Missing Email or Password";

            return false;
        }

        try {
            $this->sentry->authenticate(
                [
                    'email'=>$user,
                    'password'=>$password,
                ],
                false
            );
        } catch (UserNotFoundException $e) {
            $this->authenticationMessage = "Invalid Email or Password";

            return false;
        } catch (UserNotActivatedException $e) {
            $this->authenticationMessage = "Your account hasn't been activated. Did you get the activation email?";

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getViewVariables()
    {
        $variables = [];
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

    /**
     * @return string
     */
    public function getAuthenticationMessage()
    {
        return $this->authenticationMessage;
    }
}
