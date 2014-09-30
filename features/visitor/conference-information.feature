@visitor
Feature: Show conference information
  In order to decide whether or not to submit a proposal to speak
  As a visitor
  I should be able to see information about the conference

  Scenario: Visitor comes to site during call for papers
    Given I am on the homepage
    Then I should see "Create My Profile"
    And I should see that the call for papers ends on "Oct. 14th, 2014"

  @proposed
  Scenario: Visitor comes to site before the call for papers has begun
    Given the call for papers begins next week
    When I visit the homepage
    Then I should not see "Create My Profile"
    And I should not see "Submissions accepted until"

  @proposed
  Scenario: Visitor comes to site after call for papers has closed
    Given the call for papers has ended
    When I visit the homepage
    Then I should see "Sorry"
