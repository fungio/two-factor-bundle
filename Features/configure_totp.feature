@configure @totp
Feature: Configure TOTP (Time Based One-Time Password) Channel
  In order to use second factor authentication
  As a logged user
  I have to configure TOTP channel in my dashboard

  Background:
    Given Account created
    And Second factor enabled
    And I am logged in as "admin" with password "adminpass"

  @javascript
  Scenario: Display TOTP configuration page
    Given I am on "/2fas/configure/totp"
    Then I should see "2FAS - Two Factor Authentication Service"
    And I should see "Enable 2FA (TOTP) for your account"

  @javascript
  Scenario: Show TOTP Secret
    Given I am on "/2fas/configure/totp"
    And I wait for 1 seconds
    And I press "Enter private key manually"
    Then I should see totp-secret

  @javascript @ajax
  Scenario: Reload QR Code
    Given I am on "/2fas/configure/totp"
    And I wait for 1 seconds
    Then I should see qr-code
    And I press "Reload QR Code"
    And I wait for 5 seconds
    And I should see new qr-code

  @javascript
  Scenario: Try enable TOTP with empty code
    Given I am on "/2fas/configure/totp"
    Then I fill in "Enter your token" with " "
    And I press "Submit"
    And I should see "This value should be valid 2FAS code."

  @javascript
  Scenario: Try enable TOTP with invalid format code
    Given I am on "/2fas/configure/totp"
    Then I fill in "Enter your token" with "543hjkf65$"
    And I press "Submit"
    And I should see "This value should be valid 2FAS code."

  @javascript
  Scenario: Try enable TOTP with invalid code
    Given I am on "/2fas/configure/totp"
    Then I fill in "Enter your token" with "654321"
    And I press "Submit"
    And I should see "Code is invalid, please try again."

  @javascript
  Scenario: Enable TOTP with valid code
    Given I am on "/2fas/configure/totp"
    Then I fill in "Enter your token" with "123456"
    And I press "Submit"
    And I should see "TOTP has been configured successfully."