Feature: A Speaker submits a talk

    Background:
        Given I have signed up
        And my account is active
        And I have previously completed my profile
        And I have logged in

    Scenario: Successfully submits a talk
        Given I am on the dashboard
        When I submit a talk about "Testing! Do the Behat Dance!"
        Then I should see "Testing! Do the Behat Dance!"
          And I should be able to edit my talk
