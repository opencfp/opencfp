@visitor @wip
Feature: Visitor registers as a speaker
  In order to submit talks as part of the call for papers process
  As a visitor
  I need to be able to create a speaker profile

  Scenario: Visitor starts a new profile from homepage
    Given the call for papers has begun
    And I am on the homepage
    When I follow "Create my profile"
    Then I see "Create your OpenCFP profile below"

  Scenario: Visitor completes a new profile
    Given I am at the create profile page
    And I have filled out a valid profile
    When I press "Create my speaker profile"
    Then I see "Success: You've successfully created your account!"

  Scenario: Visitor attempts to register after call for papers has ended
    Given the call for papers has ended
    When I visit the create profile page
    Then I should see "Sorry! The call for papers has ended."