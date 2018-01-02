Feature: A Speaker signs up

    Scenario: Speaker successfully signs up
        Given I am on the signup page
        When I fill in my account information
          And I agree to the code of conduct
          And I press "Signup"
        Then I should see "Welcome to OpenCFP!"

    Scenario: Invalid signup submission
        Given I am on the signup page
        When I press "Signup"
        Then I am informed about a failed signup
