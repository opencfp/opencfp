Feature: A Speaker logs in

    Background:
        Given I have signed up
          And my account is active

    Scenario: Speaker has not completed profile
        Given I am on the login page
        When I fill in my account information
          And I login
        Then I should be guided to start my profile

    Scenario: Profile is complete, but no talks yet
        Given I have previously completed my profile
          And I am on the login page
        When I fill in my account information
          And I login
        Then I should be guided to submit my first talk
