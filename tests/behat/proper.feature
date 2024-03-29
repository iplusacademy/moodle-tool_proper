@tool @tool_proper
Feature: Proper settings
  In order to use this plugin
  As an administrator
  I need to be able to configure different settings

  Background:
    Given I log in as "admin"
    And I set the following administration settings values:
      | First name | Proper |

  Scenario: See all name fields
    When I navigate to "Users > Accounts > Replace names with proper names" in site administration
    Then I should see "proper_firstname"
    And I should see "proper_lastname"
    And I should see "proper_city"

  Scenario: Name fields should be updated
    When the following "users" exist:
      | username | firstname | lastname | email                |
      | user1    | AAAAAA    | BBBB     | teacher1@example.com |
    And I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "AAAAA"
    And I trigger cron
    And I run all adhoc tasks
    And I am on site homepage
    And I navigate to "Development > Purge caches" in site administration
    And I press "Purge all caches"
    And I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "Aaaaa"
