$require_google_plus_script
<div class="google-plus-button">
    <div class="g-plusone" data-size="<% if $gpSize %>$gpSize<% else %>small<% end_if %>" data-href="<% if $gpLink %>$gpLink<% else_if $SiteConfig.GooglePlus_Username %>http://plus.google.com/$SiteConfig.GooglePlus_Username<% else %>$absoluteBaseURL<% end_if %>"></div>
</div>