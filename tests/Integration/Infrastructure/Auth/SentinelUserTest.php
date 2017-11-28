<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Infrastructure\Auth;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelUser
 */
class SentinelUserTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @var SentinelUser
     */
    private static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $account = new SentinelAccountManagement((new Sentinel())->getSentinel());
        $account->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        self::$user = $account->findByLogin('test@example.com');
        $account->promoteTo('test@example.com', 'Speaker');
    }

    /**
     * @test
     */
    public function getIdWorks()
    {
        $this->assertSame(1, self::$user->getId());
    }

    /**
     * @test
     */
    public function getLoginWorks()
    {
        $this->assertSame('test@example.com', self::$user->getLogin());
    }

    /**
     * @test
     */
    public function hasAccessWorks()
    {
        $user = self::$user;
        $this->assertSame(false, $user->hasAccess('Admin'));
        $this->assertSame(false, $user->hasAccess('Reviewer'));
        $this->assertSame(true, $user->hasAccess('Speaker'));
        $this->assertSame(false, $user->hasAccess('NotExistingThing'));
    }

    /**
     * @test
     */
    public function checkPasswordWorks()
    {
        $this->assertTrue(self::$user->checkPassword('secret'));
    }

    /**
     * @test
     */
    public function checkResetPasswordCodeWorks()
    {
        /** @var Capsule $capsule */
        $capsule = $this->app[Capsule::class];
        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => self::$user->getId(),
            'code'    => 'secret.reset.code',
        ]);
        $this->assertFalse(self::$user->checkResetPasswordCode('wrong.code'));
        $this->assertTrue(self::$user->checkResetPasswordCode('secret.reset.code'));
        //reset after test.
        $capsule->getConnection()->query()->from('reminders')->truncate();
    }

    /**
     * @test
     */
    public function getResetPassWordCodeWorks()
    {
        /** @var Capsule $capsule */
        $capsule = $this->app[Capsule::class];
        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => self::$user->getId(),
            'code'    => 'secret.reset.code',
        ]);
        $this->assertTrue(self::$user->checkResetPasswordCode('secret.reset.code'));

        // The function resets the password code when it requests it.
        $code = self::$user->getResetPasswordCode();
        $this->assertNotSame('secret.reset.code', $code);
        $databaseEntry = $capsule->getConnection()
            ->query()
            ->from('reminders')
            ->where('user_id', self::$user->getId())
            ->get()->last();
        $this->assertSame($databaseEntry->code, $code);
        $capsule->getConnection()->query()->from('reminders')->truncate();
    }

    /**
     * @test
     */
    public function attemptResetPasswordWorks()
    {
        /** @var Capsule $capsule */
        $capsule = $this->app[Capsule::class];
        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => self::$user->getId(),
            'code'    => 'secret.reset.code',
        ]);

        $result = self::$user->attemptResetPassword('wrong code', 'newPass1');
        $this->assertFalse($result);
        $secondTry = self::$user->attemptResetPassword('secret.reset.code', 'newPass2');
        $this->assertTrue($secondTry);
        $this->assertTrue(self::$user->checkPassword('newPass2'));
    }
}
