$require_google_plus_script
<div class="google-follow-button">
    <div class="g-follow" data-annotation="<% if $gbAnnotation %>$gbAnnotation<% else %>bubble<% end_if %>" data-height="<% if $gpSize %>$gpSize<% else %>24<% end_if %>" data-href="<% if $gpLink %>$gpLink<% else_if $SiteConfig.GooglePlus_Username %>http://plus.google.com/$SiteConfig.GooglePlus_Username<% else %>$absoluteBaseURL<% end_if %>"<% if $isAuthor %> data-rel="author"<% end_if %>></div>
</div>