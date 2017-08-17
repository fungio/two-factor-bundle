@remember-me
Feature: Remember Me functionality
  In order to visit secured area without second factor
  As a remembered user
  I need to be able add my device to trusted devices list

  Background:
    Given Account created
    And Second factor enabled

  @javascript
  Scenario: Login with second factor when "Remember me" is checked on first login form
    Given I am logged in as "user_totp" with password "userpass" and remember me checked
    Then I fill in "Enter your token" with "123456"
    And I press "Login"
    And I restart session with "REMEMBERME"
    And I go to "/2fas/index"
    Then I should see "Enter your token"
    And I fill in "Enter your token" with "123456"
    And I press "Login"
    And I am on "/2fas/index"
    Then I should see "2FAS - Two Factor Authentication Service"

  @javascript
  Scenario: Login without second factor when "Remember me" is checked on second login form
    Given I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "123456"
    And I check "This is my private computer, please don't ask me for my token next time."
    And I press "Login"
    And I go to "/2fas/logout"
    And I restart session with "TWOFAS_REMEMBERME"
    And I am logged in as "user_totp" with password "userpass"
    And I go to "/2fas/index"
    Then I should see "2FAS - Two Factor Authentication Service"

  @javascript
  Scenario: Login without second factor when all "Remember me" has checked
    Given I am logged in as "user_totp" with password "userpass" and remember me checked
    Then I fill in "Enter your token" with "123456"
    And I check "This is my private computer, please don't ask me for my token next time."
    And I press "Login"
    And I restart session with "REMEMBERME,TWOFAS_REMEMBERME"
    And I go to "/2fas/index"
    Then I should see "2FAS - Two Factor Authentication Service"