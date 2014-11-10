@speaker
Feature: Speaker authentication
  In order to submit and manage my talks
  As a speaker
  I need to be able to authenticate

  Background:
    Given there is a speaker registered under "speaker@opencfp.org" with a password "secrets"

  Scenario: Successfully authenticating with correct credentials
    Given I am on the login page
    When I provide my correct credentials
    And I press "Login"
    Then I should see "My profile"
    And I should see "Sample User"

  Scenario: Fails to authenticate due to incorrect credentials
    Given I am on the login page
    When I provide incorrect credentials
    And I press "Login"
    Then I should see "Invalid Email or Password"

  Scenario: Speaker forgets their password
    Given I am on the login page
    When I follow "Forgot your password?"
    And I fill in "forgot[email]" with "speaker@opencfp.org"
    And I press "Reset my password"
    Then the forgot password email should be sent
    And I should see "An email giving you a link to reset your password has been sent"