Given(/^I see the internal survey$/) do
  expect(on(ArticlePage).survey_element.when_present).to be_visible
end

Given(/^I answer the internal survey in the positive$/) do
  on(ArticlePage).survey_yes_element.when_present.click
end

Then(/^I get thanks for my internal survey feedback$/) do
  expect(on(ArticlePage).survey_complete_element.when_present).to be_visible
end
