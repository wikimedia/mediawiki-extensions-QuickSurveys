@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant
Feature: Internal surveys
  Background:
    Given the quick survey test pages are installed

  @integration
  Scenario: Internal survey is visible with flag
    And I am on the "Quick survey test 1" page with the quick survey flag enabled
    Then I should see the survey

  @integration
  Scenario: Internal survey is not present without flag
    And I am on the "Quick survey test 1" page
    And the page has fully loaded
    And I'm not bucketed
    Then I should not see the survey
