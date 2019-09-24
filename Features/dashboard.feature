@dashboard
Feature: Manage Two Factor Authentication Service
  In order to show my 2FAS settings
  As a logged user
  I need to be able display it in my dashboard

  Background:
    Given Account created

  @javascript
  Scenario: Displaying dashboard when 2FAS disabled
    Given I am logged in as "admin" with password "adminpass"
    Then I am on "/2fas/index"
    And I should see "2FAS - Two Factor Authentication Service"
    And I should see "Two Factor Authentication Status: Disabled"
    And I should see that all channels are disabled

  @javascript
  Scenario: Displaying dashboard when 2FAS enabled
    Given I am logged in as "admin" with password "adminpass"
    And Second factor enabled
    And I am on "/2fas/index"
    And I should see "2FAS - Two Factor Authentication Service"
    And I should see "Two Factor Authentication Status: Enabled"
    And I should see that all channels are disabled

  @javascript @totp
  Scenario: Displaying dashboard when active channel is TOTP
    Given I am logged in as "user_totp" with password "userpass"
    And I am on "/2fas/index"
    And I should see "2FAS - Two Factor Authentication Service"
    And I should see "Two Factor Authentication Status: Disabled"
    And I should see that "totp" channel is "enabled"

  @javascript @totp
    Scenario: Enable TOTP channel
    Given I am logged in as "user_totp" with password "userpass"
    And Channel "totp" for user "user_totp" is disabled
    And I am on "/2fas/index"
    Then I should see that "totp" channel is "disabled"
    When I press "Enable totp"
    Then I should see "Channel has been enabled"
    And I should see that "totp" channel is "enabled"

  @javascript @totp
  Scenario: Disable TOTP channel
    Given I am logged in as "user_totp" with password "userpass"
    And I am on "/2fas/index"
    Then I should see that "totp" channel is "enabled"
    When I press "Disable totp"
    Then I should see "Channel has been disabled"

  @javascript
  Scenario: Empty trusted devices list
    Given Second factor enabled
    And I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "123456"
    And I press "Login"
    And I go to "/2fas/index"
    And I should see "You don't have any trusted devices"

  @javascript
  Scenario: Show trusted devices list when i check "Remember me" on second login form
    Given Second factor enabled
    And I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "123456"
    And I check "This is my private computer, please don't ask me for my token next time."
    And I press "Login"
    And I go to "/2fas/index"
    And I should not see "You don't have any trusted devices"
    And I should see new trusted device in list

  @javascript
  Scenario: Remove trusted device
    Given Second factor enabled
    And I am logged in as "user_totp" with password "userpass"
    Then I fill in "Enter your token" with "123456"
    And I check "This is my private computer, please don't ask me for my token next time."
    And I press "Login"
    And I go to "/2fas/index"
    When I press "Remove"
    Then I should see "Trusted device has been removed successfully."
    And I should see "You don't have any trusted devices"
