<div class="addthis_toolbox addthis_default_style" addthis:url="<% if $addThisUrl %>$addThisUrl<% else_if $Top.Link %>{$absoluteBaseURL}{$Top.Link}<% else %>$absoluteBaseURL<% end_if %>"<% if $addThisTitle %> addthis:title="$addThisTitle"<% end_if %>>
	<a class="addthis_button_facebook"></a>
	<a class="addthis_button_twitter"></a>
	<a class="addthis_button_pinterest_share"></a>
	<a class="addthis_button_email"></a>
	<a class="addthis_button_compact"></a><% if $addThisCounter %> <a class="addthis_counter addthis_bubble_style"></a><% end_if %>
</div>

$addThisJS($addThisProfileID)