<?php

namespace OpenCFP;

use Cartalyst\Sentry\Users\UserNotFoundException;
use Mockery as m;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    private $credentials;

    protected function setUp()
    {
        $this->credentials = array(
            'email'    => 'foo@bar.com',
            'password' => 'baz_bat',
        );
    }

    public function testAuthenticateInvalid()
    {
        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->
            shouldReceive('authenticate')->
            with($this->credentials, false)->
            once()->
            andThrow(new UserNotFoundException);

        $login = new Login($sentry);
        $authenticated = $login->authenticate(
            $this->credentials['email'],
            $this->credentials['password']
        );
        $this->assertFalse($authenticated);
        $this->assertNotEmpty($login->getAuthenticationMessage());
    }

    public function testAuthenticateValid()
    {
        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->
            shouldReceive('authenticate')->
            with($this->credentials, false)->
            once()->
            andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));

        $login = new Login($sentry);
        $authenticated = $login->authenticate(
            $this->credentials['email'],
            $this->credentials['password']
        );
        $this->assertTrue(!!$authenticated, "User is authenticated");
        $this->assertEmpty($login->getAuthenticationMessage());
    }

    public function testGetVariables()
    {
        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $login = new Login($sentry);
        $variables = $login->getViewVariables();
        $this->assertEmpty($variables);
    }

    public function testGetVariablesBadLogin()
    {
        $_REQUEST['email'] = $this->credentials['email'];
        $_REQUEST['passwd'] = $this->credentials['password'];

        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->
            shouldReceive('authenticate')->
            with($this->credentials, false)->
            once()->
            andThrow(new UserNotFoundException);

        $login = new Login($sentry);
        $variables = $login->getViewVariables();
        $this->assertArrayHasKey('errorMessage', $variables);
        $this->assertArrayHasKey('email', $variables);
        $this->assertEquals(
            $this->credentials['email'],
            $variables['email']
        );
    }

    public function testGetVariablesGoodLogin()
    {
        $_REQUEST['email'] = $this->credentials['email'];
        $_REQUEST['passwd'] = $this->credentials['password'];

        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->
            shouldReceive('authenticate')->
            with($this->credentials, false)->
            once()->
            andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));

        $login = new Login($sentry);
        $variables = $login->getViewVariables();
        $this->assertArrayHasKey('redirect', $variables);
    }
}
