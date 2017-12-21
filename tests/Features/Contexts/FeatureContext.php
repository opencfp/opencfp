<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Features\Contexts;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Illuminate\Database\Capsule;
use Illuminate\Database\Connection;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class FeatureContext extends MinkContext
{
    use KernelDictionary;

    /**
     * @BeforeScenario
     */
    public function beforeScenarios()
    {
        $this->databaseConnection()->unprepared('DROP DATABASE IF EXISTS`cfp_test`; CREATE DATABASE `cfp_test`; USE `cfp_test`;');
        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $phinx->run(new StringInput("migrate -e testing"), new NullOutput());
    }

    /**
     * @AfterScenario
     */
    public function rollbackTransaction()
    {
        $this->databaseConnection()->rollBack();
    }

    private function databaseConnection(): Connection
    {
        /** @var Capsule\Manager $capsule */
        $capsule = $this->getContainer()->get(Capsule\Manager::class);

        return $capsule->getConnection();
    }

    /**
     * @Given I am on the signup page
     */
    public function iAmOnTheSignupPage()
    {
        $this->visit("signup");
    }

}
