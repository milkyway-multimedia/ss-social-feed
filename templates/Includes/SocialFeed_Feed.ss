<% if $Feed %>
<% loop $Feed %>
    $forTemplate
<% end_loop %>
<% else %>
	<p class="alert alert-none wow subtle-bounce"><% _t('NO_POSTS_TO_SHOW', 'No posts to show') %></p>
<% end_if %>