@check-code
Feature: Use second factor authentication
  In order to login with second factor
  As a logged user
  I have to my account configured and 2FA is enabled

  Background:
    Given Account created
    And Second factor enabled

  @javascript
  Scenario: Login without second factor
    Given I am logged in as "admin" with password "adminpass"
    Then I should not see "Enter your token"

  @javascript @totp
  Scenario: Try login with TOTP with empty code
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with " "
    And I press "Login"
    And I should see "This value should be valid 2FAS code."

  @javascript @totp
  Scenario: Try login with TOTP with invalid format code
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "543hjkf65$"
    And I press "Login"
    And I should see "This value should be valid 2FAS code."

  @javascript @totp
  Scenario: Try login with TOTP with invalid code
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "654321"
    And I press "Login"
    And I should see "Code is invalid, please try again."

  @javascript @totp
  Scenario: Try login with TOTP with invalid code after 4 failed attempts
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "987654"
    And I press "Login"
    And I should see "Code is invalid and authentication is closed, please try again later."

  @javascript @totp
  Scenario: Login with TOTP with valid code
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "123456"
    And I press "Login"
    And I am on "/2fas/index"
    And I should see "2FAS - Two Factor Authentication Service"