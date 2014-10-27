@admin @wip
Feature: Dashboard overview
  In order to be able to see recent talks and see the selection process at-a-glance
  As an admin
  I should have a dashboard that show recent talks

  Background:
    Given there is a speaker with 4 recent talks
    And another speaker with 2 older talks
    And I am logged in as an admin

  Scenario: An admin checks out the dashboard
    Given I am on the dashboard
    Then I should see 6 total talks
    And I should see 2 total speakers
    And I should see 0 total favorite talks
    And I should see 0 total selected talks

  Scenario: An admin favorites a talk

  Scenario: An admin selects a talk
