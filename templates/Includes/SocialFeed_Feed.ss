<% if $Feed %>
<% loop $Feed %>
    <% include SocialFeed_Post %>
<% end_loop %>
<% else %>
	<p><% _t('NO_POSTS_TO_SHOW', 'No posts to show') %></p>
<% end_if %>