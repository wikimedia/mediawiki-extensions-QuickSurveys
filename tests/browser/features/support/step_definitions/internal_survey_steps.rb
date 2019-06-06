Given(/^I see the internal survey$/) do
  expect(on(ArticlePage).survey_element.when_present).to be_visible
end

Given(/^I see the internal survey with freeform text$/) do
  expect(on(ArticlePage).survey_element.when_present).to be_visible
  expect(on(ArticlePage).freeform_text_element.when_present).to be_visible
end

Given(/^I answer the internal survey in the positive$/) do
  on(ArticlePage).survey_button_one_element.when_present.click
end

Given(/^I submit a freeform answer to the internal survey with freeform text$/) do
  on(ArticlePage).freeform_text_element.when_present.send_keys('my answer')
  on(ArticlePage).submit_button_element.when_present.click
end

Then(/^I get thanks for my internal survey feedback$/) do
  expect(on(ArticlePage).final_panel_element.when_present).to be_visible
end

Then(/^the survey should have four buttons$/) do
  expect(on(ArticlePage).survey_button_one_element.when_present).to be_visible
  expect(on(ArticlePage).survey_button_two_element.when_present).to be_visible
  expect(on(ArticlePage).survey_button_three_element.when_present).to be_visible
  expect(on(ArticlePage).survey_button_four_element.when_present).to be_visible
end
