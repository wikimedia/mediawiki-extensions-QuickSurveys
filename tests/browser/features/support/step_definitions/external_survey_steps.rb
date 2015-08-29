Given(/^I see the external survey$/) do
  expect(on(ArticlePage).survey_element.when_present).to be_visible
end

Given(/^I answer the external survey in the negative$/) do
  on(ArticlePage).external_survey_no_element.when_present.click
end

Then(/^I get thanks for my external survey feedback$/) do
  expect(on(ArticlePage).survey_complete_element.when_present).to be_visible
end
