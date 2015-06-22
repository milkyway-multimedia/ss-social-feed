<% if $tweetId %>
$require_twitter_script
<span class="twitter-btn">
<a href="https://twitter.com/intent/favorite?tweet_id=$tweetId" class="twitter-like-button" <% if $twitterLargeBtn %>data-size="large" <% end_if %>data-related="<% if $twitterUser %>$twitterUser<% else_if $SiteConfig.Twitter_Username %>$SiteConfig.Twitter_Username<% else %>mwmdesign<% end_if %>" data-lang="$localeLanguage"<% if not $twitterHideCount %> data-show-count="true"<% end_if %>><% if $FavouritesDescriptor %>$Favourites $FavouritesDescriptor<% else %><% _t('FAVOURITE', 'Favourite') %><% end_if %></a></span>
<% end_if %>