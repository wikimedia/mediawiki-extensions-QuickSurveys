# Don't add the @en.m.wikipedia.beta.wmflabs.org tag
# These browser tests only need to run per commit @integration
@chrome @firefox @vagrant @integration
Feature: External surveys
  Background:
    Given the quick survey test pages are installed
      And I have dismissed survey "internal-example-survey"

  Scenario: External survey is visible with flag
    And I am on the "Quick survey test page stub" page with the external quick survey flag enabled
    And the page has fully loaded
    And the survey code has fully loaded
    Then I should see the survey

  Scenario: External survey is not present without flag when not bucketed
    And I am not bucketed for "external-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
    Then I should not see the survey

  Scenario: External survey is not present when dismissed
    And I have dismissed survey "external-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
    Then I should not see the survey

  Scenario: User can participate in external survey
    And I am on the "Quick survey test page stub" page with the external quick survey flag enabled
      And the page has fully loaded
      And the survey code has fully loaded
      And I see the external survey
    When I answer the external survey in the negative
    Then I get thanks for my external survey feedback

