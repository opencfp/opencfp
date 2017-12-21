@javascript
Feature: A Speaker deletes a talk

    Background:
        Given I have signed up
        And my account is active
        And I have previously completed my profile
        And I have logged in
        And I have submitted a talk about "Testing"

    Scenario: Successfully submits a talk
        Given I am on the dashboard
        When I delete the "Testing" talk
        Then I should not see "Testing"
