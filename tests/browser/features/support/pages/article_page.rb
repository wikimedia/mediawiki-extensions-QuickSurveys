class ArticlePage
  include PageObject

  page_url "<%= URI.encode(params[:article_name]) %><%=params[:query_string]%><%= params[:hash] %>"
  div(:survey, css: '.panel')
end
