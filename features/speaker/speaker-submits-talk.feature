Feature: Talk Submission
  In order to be considered as a speaker at the conference
  As a speaker
  I should be able to submit a proposal to speak

  Background:
    Given I have authenticated with correct credentials

  Scenario: Speaker starts to submit a talk from dashboard
    Given I am on the dashboard page
    When I press "Submit a talk"
    Then I should see "Create your talk"

  Scenario: Speaker submits a new talk
    Given I am at the talk submission page
    And I have filled out the form with valid information
    When I press "Submit my talk!"
    Then I should see "Successfully added talk"
    And I should see the talk I submitted previously

  Scenario: Speaker submits an invalid talk
    Given I am at the talk submission page
    When I make a submission without filling in "title"
    Then I should see "Please fill in the title"