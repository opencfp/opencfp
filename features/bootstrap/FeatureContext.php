<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    private $credentials = [];

    /** @BeforeScenario */
    public function setWindowSize()
    {
        $this->getSession()->resizeWindow(1920, 1080, 'current');
    }

    /** @AfterScenario */
    public function resetDatabase()
    {
        shell_exec('sh tools/refresh-database.sh');
    }

    /**
     * @Given there is a speaker registered under :username with a password :password
     */
    public function thereIsASpeakerRegisteredWith($username, $password)
    {
        array_push($this->credentials, [
            'username' => $username,
            'password' => $password
        ]);

        $this->iAmAtTheCreateProfilePage();
        $this->fillField('email', $username);
        $this->fillField('password', $password);
        $this->fillField('password2', $password);
        $this->fillField('first_name', 'Sample');
        $this->fillField('last_name', 'User');
        $this->pressButton('Create my speaker profile');
    }

    /**
     * @Given I am on the login page
     */
    public function iAmOnTheLoginPage()
    {
        $this->visit('login');
    }

    /**
     * @Given I have authenticated with correct credentials
     */
    public function iHaveAuthenticatedWithCorrectCredentials()
    {
    }

    /**
     * @When I provide my correct credentials
     */
    public function iProvideMyCorrectCredentials()
    {
        $credentials = array_pop($this->credentials);
        $this->fillField('email', $credentials['username']);
        $this->fillField('password', $credentials['password']);
    }

    /**
     * @When I provide incorrect credentials
     */
    public function iProvideIncorrectCredentials()
    {
        $this->fillField('email', 'fake@opencfp.org');
        $this->fillField('password', 'wrongpassword');
    }

    /**
     * @Then the forgot password email should be sent
     */
    public function theForgotPasswordEmailShouldBeSent()
    {
    }

    /**
     * @Given I am on the dashboard page
     */
    public function iAmOnTheDashboardPage()
    {
        throw new PendingException();
    }

    /**
     * @Given I am at the talk submission page
     */
    public function iAmAtTheTalkSubmissionPage()
    {
        throw new PendingException();
    }

    /**
     * @Given I have filled out the form with valid information
     */
    public function iHaveFilledOutTheFormWithValidInformation()
    {
        throw new PendingException();
    }

    /**
     * @Then I should see the talk I submitted previously
     */
    public function iShouldSeeTheTalkISubmittedPreviously()
    {
        throw new PendingException();
    }

    /**
     * @When I make a submission without filling in :arg1
     */
    public function iMakeASubmissionWithoutFillingIn($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given there is a speaker with :arg1 recent talks
     */
    public function thereIsASpeakerWithRecentTalks($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given another speaker with :arg1 older talks
     */
    public function anotherSpeakerWithOlderTalks($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given I am logged in as an admin
     */
    public function iAmLoggedInAsAnAdmin()
    {
        throw new PendingException();
    }

    /**
     * @Given I am on the dashboard
     */
    public function iAmOnTheDashboard()
    {
        throw new PendingException();
    }

    /**
     * @Then I should see :arg1 total talks
     */
    public function iShouldSeeTotalTalks($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I should see :arg1 total speakers
     */
    public function iShouldSeeTotalSpeakers($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I should see :arg1 total favorite talks
     */
    public function iShouldSeeTotalFavoriteTalks($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I should see :arg1 total selected talks
     */
    public function iShouldSeeTotalSelectedTalks($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I visit the homepage
     */
    public function iVisitTheHomepage()
    {
        $this->iAmOnHomepage();
    }

    /**
     * @Then I should see that the call for papers ends on :date
     */
    public function iShouldSeeThatTheCallForPapersEndsOn($date)
    {
        $this->assertPageContainsText("Submissions accepted until 11:59 PM EST $date");
    }

    /**
     * @Given the call for papers has begun
     * @Given the call for papers begins next week
     * @Given the call for papers has ended
     */
    public function theCallForPapersHasEnded()
    {
    }

    /**
     * @Then I see :textOnPage
     */
    public function iSee($textOnPage)
    {
        $this->assertPageContainsText($textOnPage);
    }

    /**
     * @Given I am at the create profile page
     * @When I visit the create profile page
     */
    public function iAmAtTheCreateProfilePage()
    {
        $this->visit('signup');
    }

    /**
     * @Given I have filled out a valid profile
     */
    public function iHaveFilledOutAValidProfile()
    {
        $this->fillField('email', 'speaker@fake.com');
        $this->fillField('password', 'secrets');
        $this->fillField('password2', 'secrets');
        $this->fillField('first_name', 'Sample');
        $this->fillField('last_name', 'User');
    }

    /**
     * @Given I forgot to fill out my :fieldName
     */
    public function iForgotToFillOutMy($fieldName)
    {
        $this->fillField($fieldName, '');
    }
}
