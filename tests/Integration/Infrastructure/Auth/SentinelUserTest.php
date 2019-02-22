<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
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
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class SentinelUserTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @var SentinelUser
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();

        $account = new SentinelAccountManagement((new Sentinel())->getSentinel());
        $account->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);

        $this->user = $account->findByLogin('test@example.com');

        $account->promoteTo('test@example.com', 'Speaker');
    }

    /**
     * @test
     */
    public function hasAccessWorks()
    {
        $this->assertFalse($this->user->hasAccess('Admin'));
        $this->assertFalse($this->user->hasAccess('Reviewer'));
        $this->assertTrue($this->user->hasAccess('Speaker'));
        $this->assertFalse($this->user->hasAccess('NotExistingThing'));
    }

    /**
     * @test
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
        /** @var Capsule $capsule */
        $capsule = $this->container->get(Capsule::class);

        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => $this->user->getId(),
            'code'    => 'secret.reset.code',
        ]);

        $this->assertFalse($this->user->checkResetPasswordCode('wrong.code'));
        $this->assertTrue($this->user->checkResetPasswordCode('secret.reset.code'));
    }

    /**
     * @test
     */
    public function getResetPassWordCodeWorks()
    {
        /** @var Capsule $capsule */
        $capsule = $this->container->get(Capsule::class);

        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => $this->user->getId(),
            'code'    => 'secret.reset.code',
        ]);

        $this->assertTrue($this->user->checkResetPasswordCode('secret.reset.code'));

        $code = $this->user->getResetPasswordCode();

        $this->assertNotSame('secret.reset.code', $code);

        $databaseEntry = $capsule->getConnection()
            ->query()
            ->from('reminders')
            ->where('user_id', $this->user->getId())
            ->get()->last();

        $this->assertSame($databaseEntry->code, $code);
    }

    /**
     * @test
     */
    public function attemptResetPasswordWorks()
    {
        /** @var Capsule $capsule */
        $capsule = $this->container->get(Capsule::class);

        $capsule->getConnection()->query()->from('reminders')->insert([
            'user_id' => $this->user->getId(),
            'code'    => 'secret.reset.code',
        ]);

        $result = $this->user->attemptResetPassword('wrong code', 'newPass1');

        $this->assertFalse($result);

        $secondTry = $this->user->attemptResetPassword('secret.reset.code', 'newPass2');

        $this->assertTrue($secondTry);
        $this->assertTrue($this->user->checkPassword('newPass2'));
    }
}
