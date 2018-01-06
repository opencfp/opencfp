<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Features\Contexts;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Illuminate\Database\Capsule;
use Illuminate\Database\Connection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

final class FeatureContext extends MinkContext
{
    use KernelDictionary;

    private $accountInfo = [
        'email'    => 'speaker@example.com',
        'password' => 'speaker',
    ];

    /**
     * @BeforeScenario
     */
    public function beforeScenarios()
    {
        $_SERVER['CFP_ENV'] = 'testing';

        $this->databaseConnection()->unprepared('DROP DATABASE IF EXISTS cfp_test; CREATE DATABASE cfp_test;');
        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $phinx->run(new StringInput('migrate'), new NullOutput());
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
        $this->visit('signup');
    }

    /**
     * @When I agree to the code of conduct
     */
    public function iAgreeToTheCodeOfConduct()
    {
        $this->checkOption('coc');
    }

    /**
     * @Then I am informed about a failed signup
     */
    public function iAmInformedAboutAFailedSignup()
    {
        $this->assertPageContainsText('there was an error');
    }

    /**
     * @Given I have signed up
     */
    public function iHaveSignedUp()
    {
        /** @var AccountManagement $accountManagement */
        $accountManagement = $this->getContainer()->get(AccountManagement::class);

        $accountManagement->create($this->accountInfo['email'], $this->accountInfo['password']);
    }

    /**
     * @Given my account is active
     */
    public function myAccountIsActive()
    {
        /** @var AccountManagement $accountManagement */
        $accountManagement = $this->getContainer()->get(AccountManagement::class);
        $accountManagement->activate($this->accountInfo['email']);
    }

    /**
     * @Given I am on the login page
     */
    public function iAmOnTheLoginPage()
    {
        $this->visit('login');
    }

    /**
     * @When I fill in my account information
     */
    public function iFillInMyAccountInformation()
    {
        $this->fillFields(new TableNode([
            ['email', $this->accountInfo['email']],
            ['password', $this->accountInfo['password']],
        ]));
    }

    /**
     * @When I login
     */
    public function iLogin()
    {
        $this->pressButton('Login');
    }

    /**
     * @Then I should be guided to start my profile
     */
    public function iShouldBeGuidedToStartMyProfile()
    {
        $this->assertPageContainsText('Fill out your profile');
    }

    /**
     * @Given I have previously completed my profile
     */
    public function iHavePreviouslyCompletedMyProfile()
    {
        $user = User::where('email', $this->accountInfo['email'])->first();
        $user->update([
            'first_name'       => 'Some',
            'last_name'        => 'Speaker',
            'has_made_profile' => 1,
        ]);
    }

    /**
     * @Then I should be guided to submit my first talk
     */
    public function iShouldBeGuidedToSubmitMyFirstTalk()
    {
        $this->assertPageContainsText('Get Started');
        $this->assertPageContainsText('Submit a Talk');
    }

    /**
     * @Given I have logged in
     */
    public function iHaveLoggedIn()
    {
        $this->iAmOnTheLoginPage();
        $this->iFillInMyAccountInformation();
        $this->iLogin();
    }

    /**
     * @Given I am on the dashboard
     */
    public function iAmOnTheDashboard()
    {
        $this->visit('dashboard');
    }

    /**
     * @When I submit a talk about :title
     * @Given I have submitted a talk about :title
     */
    public function iSubmitATalkAbout($title)
    {
        $this->clickLink('Submit a Talk');
        $this->fillField('title', $title);
        $this->fillField('description', $title);
        $this->pressButton('submit');
    }

    /**
     * @Then I should be able to edit my talk
     */
    public function iShouldBeAbleToEditMyTalk()
    {
        $this->assertPageContainsText('Edit');
    }

    /**
     * @When I delete the :title talk
     */
    public function iDeleteTheTalk($title)
    {
        // This is a workaround to not having a JavaScript session
        // yet. Delete the talk and then refresh the page.
        Talk::where('title', $title)->first()->delete();
        $this->getSession()->reload();
    }
}
