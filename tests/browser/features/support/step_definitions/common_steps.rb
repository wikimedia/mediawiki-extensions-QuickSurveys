Given(/^the quick survey test pages are installed$/) do
  wikitext = "<!-- test for page with no infobox and no image -->
'''Arcathius''' ({{lang-el| ο Άρκαθίας}} means in Greek ''ruler'', <ref>Book 1</ref>
flourished second half of 2nd century BC and first half of 1st century BC) was a Prince from the
[[Kingdom of Pontus]]. He was a prince of [[Persian people|Persian]] and
[[Macedonia (Greece)|Greek Macedonian ancestry]]. Arcathius was among the sons born to
King [[Mithridates VI of Pontus]] and 
[[Laodice (sister-wife of Mithridates VI of Pontus)|his sister-wife Laodice]].
<ref>Book 1</ref>
He was born and raised in the [[Kingdom of Pontus]].

Arcathius joined his father’s generals [[Neoptolemus (Pontic general)|Neoptolemus]] and
[[Archelaus (general)|Archelaus]] with 10,000 horses which he brought from [[Lesser Armenia]]
at the commencement of the [[First Mithridatic War]] (89 BC–85 BC).
<ref>http://wikipedia.org</ref> He participated in the great
battle fought near the [[Gök River|Amnias River]] in [[Paphlagonia]] which
King [[Nicomedes IV of Bithynia]] was defeated.<ref>http://wikipedia.org</ref> 

He was a brilliant cavalry commander. In 86 BC, Arcathius invaded Macedonia with a
separate army and completely conquered the country. He then proceeded to march against
the Roman Dictator [[Lucius Cornelius Sulla]], but on his way, Arcathius died near 
Mount Tisaion.
<ref>Some book</ref>
Arcathius was a happy person in character and his father considered him as a
beloved son and as a victorious hero in war.
<ref>Some book</ref>

==References==
<references />"

  api.create_page 'Quick survey test 1', wikitext
end

Given(/^I am on the "(.*?)" page with the quick survey flag enabled$/) do |arg1|
  visit(ArticlePage, using_params: { article_name: arg1, query_string: '?quicksurvey=true' })
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
      # TODO: If this approach works well, we should implement general
      # `wait_for_resource` and `resource_ready?` helper methods in
      # mw-selenium, and document this pattern on mw.org
      browser.execute_script("return mw.loader.getState('ext.quicksurveys.init') === 'ready'")
    end
  end
end

Then(/^I should not see the survey$/) do
  expect(on(ArticlePage).survey_element).to_not be_visible
end
