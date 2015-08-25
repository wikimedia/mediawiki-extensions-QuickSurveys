Given(/^the quick survey test pages are installed$/) do
  api.create_page 'Quick survey test page without infobox nor image',
                  File.read('samples/no_infobox_or_image.wikitext')

  api.create_page 'Quick survey test page without lead section',
                  File.read('samples/no_lead_section.wikitext')

  api.create_page 'Quick survey test page with no image infobox',
                  File.read('samples/infobox_no_image.wikitext')

  api.create_page 'Quick survey test page stub',
                  File.read('samples/stub.wikitext')

  api.create_page 'Quick survey test page with image no infobox',
                  File.read('samples/image_no_infobox.wikitext')

  api.create_page 'Quick survey test page with infobox with image',
                  File.read('samples/infobox_and_image.wikitext')
end

Given(/^I am on the "(.*?)" page with the quick survey flag enabled$/) do |arg1|
  visit(ArticlePage,
        using_params: { article_name: arg1, query_string: '?quicksurvey=true' })
end

Given(/^I am on the "(.*?)" page$/) do |arg1|
  visit(ArticlePage, using_params: { article_name: arg1 })
end

Then(/^I should see the survey$/) do
  expect(on(ArticlePage).survey_element.when_present).to be_visible
end

Then(/^I'm not bucketed$/) do
  # @todo: when https://phabricator.wikimedia.org/T109518 resolved
end

Then(/^the page has fully loaded$/) do
  on(ArticlePage) do |page|
    page.wait_until do
      # Wait for JS to hijack standard link
      script = "return mw.loader.getState('ext.quicksurveys.init') === 'ready'"
      browser.execute_script(script)
    end
  end
end

Then(/^I should not see the survey$/) do
  expect(on(ArticlePage).survey_element).to_not be_visible
end
