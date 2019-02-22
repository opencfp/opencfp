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

namespace OpenCFP\Test\Integration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * This test makes sure our migrations work correctly.
 * There is no test for migrating 'down' all the way, since that is known to be broken
 */
final class MigrationsTest extends WebTestCase
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

    private function migrateTo($target = ''): BufferedOutput
    {
        $this->dropAndCreateDatabase();

        $inputArg = $target !== '' ?
            ['phinx', 'migrate', '--target=' . $target] :
            ['phinx', 'migrate'];

        $input  = new ArgvInput($inputArg);
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
        return $this->container->get(Capsule::class);
    }
}
