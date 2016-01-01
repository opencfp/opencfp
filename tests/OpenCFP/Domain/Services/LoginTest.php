<?php

namespace OpenCFP\Domain\Services;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Faker\Factory;
use Faker\Generator;
use Mockery as m;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        unset($_REQUEST);
    }

    public function testDefaults()
    {
        $sentry = $this->getSentryMock();

        $sentry->shouldNotReceive(m::any());

        $login = new Login($sentry);

        $this->assertSame('', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsFalseIfUserIsEmpty()
    {
        $faker = $this->getFaker();

        $email = '';
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry->shouldNotReceive(m::any());

        $login = new Login($sentry);

        $this->assertFalse($login->authenticate($email, $password));
        $this->assertSame('Missing Email or Password', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsFalseIfPasswordIsEmpty()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = '';

        $sentry = $this->getSentryMock();

        $sentry->shouldNotReceive(m::any());

        $login = new Login($sentry);

        $this->assertFalse($login->authenticate($email, $password));
        $this->assertSame('Missing Email or Password', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsFalseIfEmailAndPasswordAreEmpty()
    {
        $email = '';
        $password = '';

        $sentry = $this->getSentryMock();

        $sentry->shouldNotReceive(m::any());

        $login = new Login($sentry);

        $this->assertFalse($login->authenticate($email, $password));
        $this->assertSame('Missing Email or Password', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsFalseIfUserNotFound()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('authenticate')
            ->once()
            ->with(
                [
                    'email' => $email,
                    'password' => $password,
                ],
                false
            )
            ->andThrow(new UserNotFoundException())
        ;

        $login = new Login($sentry);

        $this->assertFalse($login->authenticate($email, $password));
        $this->assertSame('Invalid Email or Password', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsFalseIfUserNotActivated()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('authenticate')
            ->once()
            ->with(
                [
                    'email' => $email,
                    'password' => $password,
                ],
                false
            )
            ->andThrow(new UserNotActivatedException())
        ;

        $login = new Login($sentry);

        $this->assertFalse($login->authenticate($email, $password));
        $this->assertSame("Your account hasn't been activated. Did you get the activation email?", $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testAuthenticateReturnsTrueIfAuthenticationSucceeded()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('authenticate')
            ->once()
            ->with(
                [
                    'email' => $email,
                    'password' => $password,
                ],
                false
            )
        ;

        $login = new Login($sentry);

        $this->assertTrue($login->authenticate($email, $password));
        $this->assertSame('', $login->getAuthenticationMessage());
        $this->assertSame([], $login->getViewVariables());
    }

    public function testGetViewVariablesAuthenticateIfRequestParamsDetectedAndReturnsSuccessVariables()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('authenticate')
            ->once()
            ->with(
                [
                    'email' => $email,
                    'password' => $password,
                ],
                false
            )
        ;

        $login = new Login($sentry);

        $_REQUEST['email'] = $email;
        $_REQUEST['passwd'] = $password;

        $viewVariables = $login->getViewVariables();

        $expected = [
            'redirect' => '/dashboard',
        ];

        $this->assertSame($expected, $viewVariables);
    }

    public function testGetViewVariablesAuthenticatesIfRequestParamsDetectedAndReturnsFailureVariables()
    {
        $faker = $this->getFaker();

        $email = $faker->email;
        $password = $faker->word;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('authenticate')
            ->once()
            ->with(
                [
                    'email' => $email,
                    'password' => $password,
                ],
                false
            )
            ->andThrow(new UserNotFoundException())
        ;

        $login = new Login($sentry);

        $_REQUEST['email'] = $email;
        $_REQUEST['passwd'] = $password;

        $viewVariables = $login->getViewVariables();

        $expected = [
            'errorMessage' => 'Invalid Email or Password',
            'email' => $email,
        ];

        $this->assertSame($expected, $viewVariables);
    }

    /**
     * @return Generator
     */
    private function getFaker()
    {
        static $faker;

        if ($faker === null) {
            $faker = Factory::create();
        }

        return $faker;
    }

    /**
     * @return m\MockInterface|Sentry
     */
    private function getSentryMock()
    {
        return m::mock(Sentry::class);
    }
}
