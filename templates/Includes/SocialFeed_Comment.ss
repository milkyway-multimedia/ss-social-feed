<div class="panel-post-comment<% if $ReplyByPoster %> author-reply<% end_if %>">
    <% if $Author || $AuthorName %><h6><% if $AuthorName %>$AuthorName<% else %>$Author<% end_if %> <time>$Posted.Ago</time></h6> <% end_if %>
	<p>$Content</p>
</div>