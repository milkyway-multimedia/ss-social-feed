<% if $Feed %>
<% loop $Feed %>
    <% if not $isHidden %>
        $forTemplate
    <% end_if %>
<% end_loop %>
<% else %>
	<div class="alert alert-none wow subtle-bounce"><% _t('NO_POSTS_TO_SHOW', 'No posts to show') %></div>
<% end_if %>