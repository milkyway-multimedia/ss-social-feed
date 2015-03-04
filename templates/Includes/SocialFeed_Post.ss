<article class="panel-post $Profile.StyleClasses $StyleClasses $EvenOdd">
    <% if $Avatar %>
	<div class="avatar panel-post--avatar">
            <% if $AuthorURL %>
				<a href="$AuthorURL" target="_blank"><img src="$Avatar" alt="$Author" class="panel-post--avatar-image" /></a>
            <% else %>
				<img src="$Avatar" alt="$Author" class="panel-post--avatar-image" />
            <% end_if %>
	</div>
    <% end_if %>

	<div class="panel-post-body">
		<h4 class="panel-post-body--header">
            <% if $AuthorURL %>
				<a href="$AuthorURL" target="_blank" class="panel-post--header--icon-holder"><i class="social-icon panel-post-body--header--icon" title="<% _t('VIA', 'Via') %> $Profile.Platform"></i></a>
            <% else %>
				<i class="social-icon panel-post-body--header--icon" title="<% _t('VIA', 'Via') %> $Profile.Platform"></i>
            <% end_if %>

            <% if $canLikePage && $AuthorURL %>
                $Profile.LikeButton($AuthorURL)
            <% end_if %>

            <% if $AuthorURL %>
				<a href="$AuthorURL" target="_blank"><% if $AuthorName %>$AuthorName<% else_if $Author %>$Author<% else %>$Title<% end_if %></a>
            <% else %>
                <% if $AuthorName %>$AuthorName<% else_if $Author %>$Author<% else %>$Title<% end_if %>
            <% end_if %>

			<time class="panel-post-body--header--time">$Posted.Ago</time>
		</h4>

        <% if $Rating %>
        <p class="panel-post--rating">
            <span class="panel-post--rating-label">
                $Rating <% _t('STAR', 'Star') %>
            </span>
        </p>
        <% end_if %>

        <div class="panel-post--contents">
        $Content
        </div>

        <% include SocialFeed_Media %>

		<div class="panel-post-footer">
            <% if $Icon %><img src="$Icon" alt="$StatusType" class="post-icon panel-post-footer--icon" /> <% end_if %>

            <% if not $HideAddThis && $AddThisProfileID %>
				<div class="panel-post-share panel-post-footer--share">
                    <% if $Title %>
                        <% if $AbsoluteLink %>
                            <% include AddThis_ShareModule addThisProfileID=$AddThisProfileID,addThisTitle=$Title,addThisUrl=$AbsoluteLink %>
                        <% else %>
                            <% include AddThis_ShareModule addThisProfileID=$AddThisProfileID,addThisTitle=$Title,addThisUrl=$Link %>
                        <% end_if %>
                    <% else %>
                        <% if $AbsoluteLink %>
                            <% include AddThis_ShareModule addThisProfileID=$AddThisProfileID,addThisTitle=$Author,addThisUrl=$AbsoluteLink %>
                        <% else %>
                            <% include AddThis_ShareModule addThisProfileID=$AddThisProfileID,addThisTitle=$Author,addThisUrl=$Link %>
                        <% end_if %>
                    <% end_if %>
				</div>
            <% end_if %>

            <% if $CommentsDescriptor %>
				<span class="panel-post-comment-count panel-post-footer--comment-count"><a href="$Link" target="_blank">$CommentsCount $CommentsDescriptor</a></span>
            <% end_if %>

            <% if $ReplyDescriptor %>
				<span class="panel-post-replies-count panel-post-footer--replies-count"><a href="$Link" target="_blank">$ReplyCount $ReplyDescriptor</a></span>
            <% end_if %>

            <% if $canLikePost %>
                $Profile.LikePostButton($Link)
            <% else_if $LikesDescriptor %>
                <span class="panel-post-likes-count panel-post-footer--likes-count"><a href="$Link" target="_blank">$LikesCount $LikesDescriptor</a></span>
            <% end_if %>

            <% if $Profile.AllowPostShare %>
                <% if $AbsoluteLink %>
                    <% include Facebook_ShareButton fbLink=$AbsoluteLink %>
                <% else %>
                    <% include Facebook_ShareButton fbLink=$Link %>
                <% end_if %>
            <% end_if %>

            <% if $Profile.AllowPostSend %>
                <% if $AbsoluteLink %>
                    <% include Facebook_SendButton fbLink=$AbsoluteLink %>
                <% else %>
                    <% include Facebook_SendButton fbLink=$Link %>
                <% end_if %>
            <% end_if %>

            <% if $RetweetsDescriptor %>
				<span class="post-retweets-count panel-post-footer--retweets-count">$Retweets $RetweetsDescriptor</span>
            <% end_if %>

            <% if $UserMentionsDescriptor %>
				<span class="panel-post-mentions-count panel-post-footer--mentions-count"><% if $UserMentions %>$UserMentions.Count<% else %>0<% end_if %> $UserMentionsDescriptor</span>

                <% if $canLikePost %>
                    $Profile.LikePostButton($AuthorName)
                <% end_if %>
            <% end_if %>

            <% if $ReshareCountDescriptor %>
				<span class="panel-post-reshares-count panel-post-footer--reshares-count"><a href="$Link" target="_blank">$ReshareCount $ReshareCountDescriptor</a></span>
            <% end_if %>
		</div>

        <% if $Comments %>
			<div class="panel-post-comments">
                <% loop $Comments %>
					<% include SocialFeed_Comment %>
                <% end_loop %>
			</div>
        <% end_if %>
	</div>
</article>