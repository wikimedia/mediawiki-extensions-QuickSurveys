@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant @integration
Feature: Internal surveys
  Background:
    Given the quick survey test pages are installed
      And I have dismissed survey "external-example-survey"

  Scenario: Internal survey has multiple answers
    Given I am on the "Quick survey test page with image no infobox" page and I see the drinks survey
      Then I should see the survey
      And the survey should have four buttons

  Scenario: Internal survey is visible with flag
    And I am on the "Quick survey test page stub" page with the internal quick survey flag enabled
    Then I should see the survey

  Scenario: Internal survey is not present without flag
    And I am not bucketed for "internal-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
    Then I should not see the survey

  Scenario: Internal survey is not present when dismissed
    And I have dismissed survey "internal-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
    Then I should not see the survey

  Scenario: User can participate in internal survey
    And I am on the "Quick survey test page stub" page with the internal quick survey flag enabled
      And I see the internal survey
    When I answer the internal survey in the positive
    Then I get thanks for my internal survey feedback
