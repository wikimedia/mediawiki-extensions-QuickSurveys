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

Then(/^the page has fully loaded$/) do
  on(ArticlePage) do |page|
    page.wait_until do
      # Wait for async JS to hijack standard link
      script = 'return mw && '\
        'mw.loader.getState("ext.quicksurveys.init") === "ready";'
      page.execute_script(script)
    end
  end
end

Then(/^I should not see the survey$/) do
  expect(on(ArticlePage).survey_element).to_not be_visible
end

Then(/^I'm bucketed with storage key "(.*?)"$/) do |arg1|
  # localStorage is undefined without visiting a page
  visit(ArticlePage,
        using_params: { article_name: 'Quick survey test page stub' })
  # token 2c0cdc37f48b1b0e is bucketed with 50% coverage
  browser.execute_script("localStorage.setItem('#{arg1}','2c0cdc37f48b1b0e');")
end

Then(/^I'm not bucketed with storage key "(.*?)"$/) do |arg1|
  # localStorage is undefined without visiting a page
  visit(ArticlePage,
        using_params: { article_name: 'Quick survey test page stub' })
  # token 63e9d6d760750eaa is not bucketed with 50% coverage
  browser.execute_script("localStorage.setItem('#{arg1}','63e9d6d760750eaa');")
end

Then(/^I've dismissed the storage key "(.*?)"$/) do |arg1|
  # localStorage is undefined without visiting a page
  visit(ArticlePage,
        using_params: { article_name: 'Quick survey test page stub' })
  # Setting value for the storage key to "~" marks the survey as dismissed
  browser.execute_script("localStorage.setItem('#{arg1}','~');")
end
