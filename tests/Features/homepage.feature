Feature: Homepage

Scenario: It loads!
    Given I am on the homepage
    Then I should see "OpenCFP"
      And I should not see "Internal Server Error"
