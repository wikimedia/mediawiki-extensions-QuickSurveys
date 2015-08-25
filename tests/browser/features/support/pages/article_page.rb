# Standard article page
class ArticlePage
  include PageObject

  page_url '<%= URI.encode(params[:article_name]) %>'\
           '<%=params[:query_string]%><%= params[:hash] %>'
  div(:survey, css: '.panel')
  a(:survey_yes,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 0)
  div(:survey_complete, css: '.panel .completed')
end
