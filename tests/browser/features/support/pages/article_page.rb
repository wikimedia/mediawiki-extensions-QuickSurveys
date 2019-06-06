# Standard article page
class ArticlePage
  include PageObject

  page_url '<%= URI.encode(params[:article_name]) %>'\
           '<%= URI.encode(params[:query_string]) if params[:query_string] %>'\
           '<%= params[:hash] %>'
  div(:survey, css: '.panel')
  a(:survey_button_one,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 0)
  a(:survey_button_two,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 1)
  a(:survey_button_three,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 2)
  a(:survey_button_four,
    css: '.panel .oo-ui-buttonSelectWidget .oo-ui-buttonElement-button',
    index: 3)
  textarea(
    :freeform_text,
    css: '.panel .survey-button-container .oo-ui-textInputWidget textarea',
    index: 0)
  a(:submit_button,
    css: '.panel .oo-ui-flaggedElement-progressive .oo-ui-buttonElement-button')
  a(:external_survey_no,
    css: '.panel .oo-ui-buttonWidget .oo-ui-buttonElement-button',
    index: 1)
  div(:final_panel,
      css: '.ext-quick-survey-panel .oo-ui-panelLayout',
      index: 1)
end
