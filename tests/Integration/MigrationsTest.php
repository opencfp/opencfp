<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Test\BaseTestCase;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * This test makes sure our migrations work correctly.
 * There is no test for migrating 'down' all the way, since that is known to be broken
 *
 * @coversNothing
 */
class MigrationsTest extends BaseTestCase
{
    /**
     * @test
     */
    public function migrateUpHasNoErrors()
    {
        //We need to drop the DB beforehand, since it is seeded through a dump file normally.
        $output  = $this->migrateTo();
        $content = $output->fetch();

        //Check for some specifics first so we have a bit more info if something goes wrong
        $this->assertNotContains('[PDOException]', $content);
        $this->assertNotContains('[RuntimeException]', $content);
        $this->assertNotContains('Exception', $content);
        $this->assertContains('0 Schema', $content);
        //Please change this with the latest migration when updating.
        $this->assertContains('20171120102354 SentinelMigration', $content);
    }

    /**
     * @test
     */
    public function oldUsersGetActivated()
    {
        if (!$this->app[AccountManagement::class] instanceof SentryAccountManagement) {
            $this->markTestSkipped();
        }
        $capsule = $this->getCapsule();
        $this->migrateTo('20171120102354');
        //We are now at the migration before the users get activated

        /** @var AccountManagement $accounts */
        $accounts =$this->app[AccountManagement::class];
        $accounts->create('test@example.com', 'secret');
        $accounts->activate('test@example.com');

        $activations = $capsule->getConnection()->query()->from('activations')->get();
        $this->assertCount(0, $activations);
        $this->migrateContinue();

        $postActivations = $capsule->getConnection()->query()->from('activations')->get();
        $this->assertCount(1, $postActivations);
        $this->assertSame(1, $postActivations->first()->completed);
    }

    /**
     * @test
     */
    public function adminsGetPromoted()
    {
        if (!$this->app[AccountManagement::class] instanceof SentryAccountManagement) {
            $this->markTestSkipped();
        }
        $capsule = $this->getCapsule();
        $this->migrateTo('20171120122725');
        //We are now at the migration before the roles get done.

        /** @var AccountManagement $accounts */
        $accounts = $this->app[AccountManagement::class];
        $accounts->create('test@example.com', 'secret');
        $accounts->activate('test@example.com');
        $accounts->promoteTo('test@example.com', 'Admin');
        $accounts->create('speaker@example.com', 'secret');
        $accounts->activate('speaker@example.com');

        $userRoles = $capsule->getConnection()->query()->from('role_users')->get();
        $this->assertCount(0, $userRoles);
        $this->migrateContinue();

        $postUserRoles = $capsule->getConnection()->query()->from('role_users')->get();
        $this->assertCount(2, $postUserRoles);
        $user= $accounts->findById($postUserRoles->first()->user_id);
        $this->assertSame('test@example.com', $user->getLogin());
    }

    private function migrateTo($target = ''): BufferedOutput
    {
        $this->dropAndCreateDatabase();

        $inputArg = $target !== '' ?
            ['phinx', 'migrate', '--environment=testing', '--target=' . $target] :
            ['phinx', 'migrate', '--environment=testing'];

        $input  = new ArgvInput($inputArg);
        $output = new BufferedOutput();
        $phinx  = new PhinxApplication();

        $phinx->setAutoExit(false);
        $phinx->run($input, $output);

        return $output;
    }

    private function migrateContinue(): BufferedOutput
    {
        $input  = new ArgvInput(['phinx', 'migrate', '--environment=testing']);
        $output = new BufferedOutput();
        $phinx  = new PhinxApplication();

        $phinx->setAutoExit(false);
        $phinx->run($input, $output);

        return $output;
    }

    private function dropAndCreateDatabase()
    {
        $this->getCapsule()->getConnection()
        ->unprepared('DROP DATABASE IF EXISTS`cfp_test`; CREATE DATABASE `cfp_test`; USE `cfp_test`;');
    }

    private function getCapsule(): Capsule
    {
        return $this->app[Capsule::class];
    }
}
