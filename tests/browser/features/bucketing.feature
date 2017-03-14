# T159739: The coverage of all surveys on the Beta Cluster was set to 0 to
# reduce the noise on the Mobile 2G performance dashboard
# <https://grafana.wikimedia.org/dashboard/db/mobile-2g>. These scenarios,
# therefore, shouldn't be run against the Beta Cluster as they require that the
# "internal-" and "external-example-survey" surveys have non-zero coverage.
#
# Don't add the @en.m.wikipedia.beta.wmflabs.org tag unless the Beta Cluster
# config changes.
@chrome @firefox @vagrant @integration
  Feature: Survey bucketing (opting in)
    Background:
      Given the quick survey test pages are installed

    Scenario: Internal survey is visible when bucketed
      When I have dismissed survey "external-example-survey"
      And I am bucketed for "internal-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
      And the survey code has fully loaded
      Then I should see the survey

    Scenario: External survey is visible when bucketed
      When I have dismissed survey "internal-example-survey"
      And I am bucketed for "external-example-survey"
      And I am on the "Quick survey test page stub" page
      And the page has fully loaded
      And the survey code has fully loaded
      Then I should see the survey
