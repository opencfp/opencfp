<?php

namespace OpenCFP\Test\Integration\Infrastructure\Auth;

use OpenCFP\Domain\Model\User;
use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Infrastructure\Auth\SentryUser;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\DataBaseInteraction;
use OpenCFP\Test\Helper\SentryTestHelpers;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentryUser
 */
class SentryUserTest extends BaseTestCase
{
    use DataBaseInteraction;
    use SentryTestHelpers;

    /**
     * @var SentryAccountManagement
     */
    private $sut;

    /**
     * @var SentryUser
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->sut = new SentryAccountManagement($this->getSentry());
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $this->user = $this->sut->findByLogin('test@example.com');
    }

    /**
     * This test is more so to make sure the rest of the tests are testing the right thing.
     *
     * @test
     */
    public function weHaveTheRightUser()
    {
        $this->assertInstanceOf(UserInterface::class, $this->user);
    }

    /**
     * @test
     */
    public function getIdWorks()
    {
        $this->assertSame(1, $this->user->getId());
    }

    /**
     * @test
     */
    public function getLoginWorks()
    {
        $this->assertSame('test@example.com', $this->user->getLogin());
    }

    /**
     * @test
     */
    public function hasAccessWorks()
    {
        $this->assertFalse($this->user->hasAccess('admin'));
        $this->assertFalse($this->user->hasAccess('reviewer'));
        $this->assertSame(true, $this->user->hasAccess('users'));
        $this->assertFalse($this->user->hasAccess('blablabla'));
    }

    /**
     * @tests
     */
    public function checkPasswordWorks()
    {
        $this->assertTrue($this->user->checkPassword('secret'));
    }

    /**
     * @test
     */
    public function checkResetPasswordCodeWorks()
    {
        $this->assertFalse($this->user->checkResetPasswordCode('aasdfhd'));

        //Manualy insert a password code into the db
        $user                      = User::where('email', 'test@example.com')->first();
        $user->reset_password_code = 'hello123';
        $user->save();
        //Get the user again, since we updated the database.
        $resetCodeUser = $this->sut->findByLogin('test@example.com');
        $this->assertTrue($resetCodeUser->checkResetPasswordCode('hello123'));
    }

    /**
     * @test
     */
    public function getResetPassWordCodeWorks()
    {
        //Set up a reset code in the database
        $user                      = User::where('email', 'test@example.com')->first();
        $user->reset_password_code = 'hello123';
        $user->save();
        $resetCodeUser = $this->sut->findByLogin('test@example.com');
        $this->assertTrue($resetCodeUser->checkResetPasswordCode('hello123'));

        //This function resets the reset code when its called.
        $this->assertNotSame('hello123', $resetCodeUser->getResetPasswordCode());
    }

    /**
     * @test
     */
    public function attemptResetPasswordWorks()
    {
        //Set up a reset code in the database
        $user                      = User::where('email', 'test@example.com')->first();
        $user->reset_password_code = 'hello123';
        $user->save();
        $resetCodeUser = $this->sut->findByLogin('test@example.com');
        $this->assertTrue($resetCodeUser->checkResetPasswordCode('hello123'));

        $this->assertFalse($resetCodeUser->attemptResetPassword('asdfgj', 'newPassword123'));
        $this->assertTrue($resetCodeUser->checkPassword('secret'));

        $this->assertTrue($resetCodeUser->attemptResetPassword('hello123', 'newSecret'));
        $this->assertTrue($resetCodeUser->checkPassword('newSecret'));
    }

    /**
     * @test
     */
    public function getUserReturnsUser()
    {
        $this->assertInstanceOf(\Cartalyst\Sentry\Users\UserInterface::class, $this->user->getUser());
    }
}
