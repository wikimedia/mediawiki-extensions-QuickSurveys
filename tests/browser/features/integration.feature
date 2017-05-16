# This browser test runs on the beta cluster to protect us against upstream breakages
# for example OOjs UI
@chrome @firefox @vagrant @integration @en.m.wikipedia.beta.wmflabs.org
Feature: Integration

  Scenario: Survey is visible with quicksurvey=true flag
    Given the quick survey test pages are installed
    When I am on the "Quick survey test page stub" page with the quick survey flag enabled
      And the page has fully loaded
      And the survey code has fully loaded
    Then I should see the survey
