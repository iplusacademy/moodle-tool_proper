@tool @tool_proper
Feature: Proper settings
  In order to use this plugin
  As an administrator
  I need to be able to configure different settings

  Background:
    Given I log in as "admin"

  Scenario: See all name fields
    When I navigate to "Users > Accounts > Replace names with proper names" in site administration
    Then I should see "proper_firstname"
    And I should see "proper_lastname"
    And I should see "proper_city"
