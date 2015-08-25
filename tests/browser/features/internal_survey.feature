@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant @integration
Feature: Internal surveys
  Background:
    Given the quick survey test pages are installed

  Scenario: Internal survey is visible with flag
    And I am on the "Quick survey test page stub" page with the quick survey flag enabled
    Then I should see the survey

  Scenario: Internal survey is not present without flag
    And I'm not bucketed with storage key "ext-quicksurvey-internal-example-survey"
    And I am on the "Quick survey test page stub" page
    And the page has fully loaded
    Then I should not see the survey

  Scenario: Internal survey is visible when bucketed
    And I'm bucketed with storage key "ext-quicksurvey-internal-example-survey"
    And I am on the "Quick survey test page stub" page
    Then I should see the survey

  Scenario: Internal survey is not present when dismissed
    And I've dismissed the storage key "ext-quicksurvey-internal-example-survey"
    And I am on the "Quick survey test page stub" page
    And the page has fully loaded
    Then I should not see the survey

  Scenario: User can participate in internal survey
    And I am on the "Quick survey test page stub" page with the quick survey flag enabled
    And I see the survey
    When I answer the survey in the positive
    Then I get thanks for my feedback
