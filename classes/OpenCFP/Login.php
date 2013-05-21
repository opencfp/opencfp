<?php
namespace OpenCFP;

use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Mockery\Exception;

class Login
{
    private $sentry;
    private $authenticationMessage = '';

    function __construct($sentry)
    {
        $this->sentry = $sentry;
    }

    public function authenticate($user, $password)
    {
        try {
            $this->sentry->authenticate(
                array(
                    'email'=>$user,
                    'password'=>$password,
                ),
                false
            );
        }
        catch (UserNotFoundException $e) {
            $this->authenticationMessage = "Invalid Email or Password";
            return false;
        }
        catch (UserNotActivatedException $e) {
            $this->authenticationMessage = "Your account hasn't been activated. Did you get the activation email?";
            return false;
        }
        return true;
    }

    public function getViewVariables()
    {
        $variables = array();
        if (isset($_REQUEST['email']) && (isset($_REQUEST['passwd']))) {
            if (!$this->authenticate($_REQUEST['email'], $_REQUEST['passwd'])) {
                $variables['errorMessage'] = $this->getAuthenticationMessage();
                $variables['email'] = $_REQUEST['email'];
            } else {
                $variables['redirect'] = 'dashboard.php';
            }
        }
        return $variables;
    }

    public function getAuthenticationMessage()
    {
        return $this->authenticationMessage;
    }
}