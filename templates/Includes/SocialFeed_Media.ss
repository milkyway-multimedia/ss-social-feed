<% if $Image || $HeroShot %>
<figure class="panel-post-media has-image">
    <% if $HeroShot %>
        <img src="$HeroShot.URL" alt="<% if $Title %>$Title<% else %>$ObjectName<% end_if %>" />
    <% else %>
		<img src="$Image.URL" alt="<% if $Title %>$Title<% else %>$ObjectName<% end_if %>" />
    <% end_if %>

	<figcaption class="panel-post-media-caption">
        <% if $ObjectURL && $ObjectName %>
			<h5><a href="$ObjectURL" target="_blank">$ObjectName</a></h5>
        <% else_if $ObjectName %>
			<h5>$ObjectName</h5>
        <% end_if %>
        <% if $HeroShot && $HeroShot.Description %><p>$HeroShot.Description</p><% end_if %>
        <% if $Image && $Image.Description %><p>$Image.Description</p><% end_if %>
	</figcaption>
</figure>
<% else_if $Picture %>
	<figure class="panel-post-media has-image">
        <% if $ObjectURL %>
			<a href="$ObjectURL" target="_blank"><img src="$Picture" alt="$ObjectName" /></a>
        <% else %>
			<img src="$Picture" alt="$ObjectName" />
        <% end_if %>

		<figcaption class="panel-post-media-caption">
            <% if $ObjectURL && $ObjectName %>
				<h5><a href="$ObjectURL" target="_blank">$ObjectName</a></h5>
            <% else_if $ObjectName %>
				<h5>$ObjectName</h5>
            <% end_if %>
            <% if $Description %><p>$Description</p><% end_if %>
		</figcaption>
	</figure>
<% else_if $ObjectURL %>
	<figure class="panel-post-media">
		<a href="$ObjectURL" target="_blank">$ObjectURL</a>

		<figcaption class="panel-post-media-caption">
            <% if $ObjectURL && $ObjectName %>
				<h6><a href="$ObjectURL" target="_blank">$ObjectName</a></h6>
            <% else_if $ObjectName %>
				<h6>$ObjectName</h6>
            <% end_if %>
            <% if $Description %><p>$Description</p><% end_if %>
		</figcaption>
	</figure>
<% else_if $Attachments %>
    <% loop $Attachments %>
		<figure class="panel-post-media">
            <% if $Picture %>
                <% if $Link %>
					<a href="$Link" target="_blank"><img src="$Picture" alt="$Link" /></a>
                <% else %>
					<img src="$Picture" alt="$Link" />
                <% end_if %>
            <% end_if %>

            <% if not $Picture %>
                <% if $Content %>
					<figcaption class="panel-post-media-caption">
                        $Content
					</figcaption>
                <% end_if %>
            <% end_if %>
		</figure>
    <% end_loop %>
<% else_if $StartTime || $EndTime %>
        <div class="panel-event">
            <% if $ObjectName %>
                <h5 class="panel-event--title">
                <% if $Link %>
                    <a href="$Link" target="_blank">$ObjectName</a>
                <% else %>
                    $ObjectName
                <% end_if %>
                </h5>
            <% end_if %>
            <% if $Venue %>
                <h5 class="panel-event--venue">
                    <label class="panel-event--venue-label">At </label>
                    <strong>
                    <% if $VenueLink %>
                        <a href="$VenueLink" target="_blank">$Venue</a>
                    <% else %>
                        $Venue
                    <% end_if %>
                    </strong>
                </h5>
            <% end_if %>

            $Description
        </div>
<% end_if %>