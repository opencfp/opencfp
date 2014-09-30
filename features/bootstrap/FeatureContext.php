<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext implements SnippetAcceptingContext
{

    /**
     * @Given the call for papers has begun
     */
    public function theCallForPapersHasBegun()
    {
        throw new PendingException();
    }

    /**
     * @Given the call for papers ends on :date
     */
    public function theCallForPapersEndsOn($date)
    {
        throw new PendingException();
    }

    /**
     * @Given there is a speaker registered under :username with a password :password
     */
    public function thereIsASpeakerRegisteredWith($username, $password)
    {
        throw new PendingException();
    }

    /**
     * @Given I am on the login page
     */
    public function iAmOnTheLoginPage()
    {
        throw new PendingException();
    }

    /**
     * @Given I have authenticated with correct credentials
     */
    public function iHaveAuthenticatedWithCorrectCredentials()
    {
        throw new PendingException();
    }

    /**
     * @When I provide my correct credentials
     */
    public function iProvideMyCorrectCredentials()
    {
        throw new PendingException();
    }

    /**
     * @When I provide incorrect credentials
     */
    public function iProvideIncorrectCredentials()
    {
        throw new PendingException();
    }

    /**
     * @Then the forgot password email should be sent
     */
    public function theForgotPasswordEmailShouldBeSent()
    {
        throw new PendingException();
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
        $this->assertPageContainsText("Submissions accepted until 11:59 PM EST Oct. 14th, 2014");
    }

    /**
     * @Given the call for papers begins next week
     */
    public function theCallForPapersBeginsNextWeek()
    {
        throw new PendingException();
    }

    /**
     * @Given the call for papers has ended
     */
    public function theCallForPapersHasEnded()
    {
        throw new PendingException();
    }

    /**
     * @Then I should see "Sorry
     */
    public function iShouldSeeSorry()
    {
        throw new PendingException();
    }

    /**
     * @Then I see :textOnPage
     */
    public function iSee($textOnPage)
    {
        throw new PendingException();
    }

    /**
     * @When I visit the create profile page
     */
    public function iVisitTheCreateProfilePage()
    {
        throw new PendingException();
    }

    /**
     * @Given I am at the create profile page
     */
    public function iAmAtTheCreateProfilePage()
    {
        throw new PendingException();
    }

    /**
     * @Given I have filled out a valid profile
     */
    public function iHaveFilledOutAValidProfile()
    {
        throw new PendingException();
    }

    /**
     * @Given I forgot to fill out my :arg1
     */
    public function iForgotToFillOutMy($arg1)
    {
        throw new PendingException();
    }
}
