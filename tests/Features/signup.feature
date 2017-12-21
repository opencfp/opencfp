Feature: Signup

    Scenario: Speaker successfully signs up
        Given I am on the signup page
        When I fill in the following:
            | email | speaker@example.com |
            | password | speaker |
          And I check "coc"
          And I press "Signup"
        Then I should see "Welcome to OpenCFP!"

    Scenario: Invalid signup submission
        Given I am on the signup page
        When I press "Signup"
        Then I should see "email field is required"
        Then I should see "password field is required"
          And I should see "coc must be accepted"
