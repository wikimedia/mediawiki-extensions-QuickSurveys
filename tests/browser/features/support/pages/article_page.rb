# Standard article page
class ArticlePage
  include PageObject

  page_url '<%= URI.encode(params[:article_name]) %>'\
           '<%= URI.encode(params[:query_string]) if params[:query_string] %>'\
           '<%= params[:hash] %>'
  div(:survey, css: '.panel')
  a(:survey_yes,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 0)
  a(:external_survey_no,
    css: '.panel .oo-ui-buttonWidget .oo-ui-buttonElement-button',
    index: 1)
  div(:survey_complete, css: '.panel .completed')
end
