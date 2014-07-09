<article class="post $_is $EvenOdd">
	<div class="avatar">
        <% if $Avatar %>
            <% if $AuthorURL %>
				<a href="$AuthorURL" target="_blank"><img src="$Avatar" alt="$Author" /></a>
            <% else %>
				<img src="$Avatar" alt="$Author" />
            <% end_if %>
        <% end_if %>
	</div>

	<div class="post-body">
		<h4>
            <% if $AuthorURL %>
				<a href="$AuthorURL" target="_blank"><i class="social-icon icon-{$_is}" title="<% _t('VIA', 'Via') %> $Platform"></i></a>
            <% else %>
				<i class="social-icon icon-{$_is}" title="<% _t('VIA', 'Via') %> $Platform"></i>
            <% end_if %>

            <% if $AuthorURL %>
                <% if $canLikePage && $_is == 'facebook' %>
                    <% include Facebook_LikeButton fbLink=$AuthorURL %>
                <% else_if $canFollowAuthor && $_is == 'google-plus' %>
                    <% include Google_FollowButton gpLink=$AuthorURL %>
                <% else_if $canFollowAuthor && $_is == 'twitter' %>
                    <% include Twitter_FollowButton twitterUser=$AuthorName, twitterCount='true', twitterAlign='right' %>
                <% end_if %>
            <% end_if %>

            $Author
			<time>$Posted.Ago</time>
		</h4>

		<p>$Content</p>

        <% if $Picture %>
			<figure class="media has-image">
                <% if $ObjectURL %>
					<a href="$ObjectURL" target="_blank"><img src="$Picture" alt="$ObjectName" /></a>
                <% else %>
					<img src="$Picture" alt="$ObjectName" />
                <% end_if %>

				<figcaption class="media-caption">
                    <% if $ObjectURL && $ObjectName %>
						<h5><a href="$ObjectURL" target="_blank">$ObjectName</a></h5>
                    <% else_if $ObjectName %>
						<h5>$ObjectName</h5>
                    <% end_if %>
                    <% if $Description %><p>$Description</p><% end_if %>
				</figcaption>
			</figure>
        <% else_if $ObjectURL %>
			<figure class="media">
				<a href="$ObjectURL" target="_blank">$ObjectURL</a>

				<figcaption class="media-caption">
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
				<figure class="media">
                    <% if $Picture %>
                        <% if $Link %>
							<a href="$Link" target="_blank"><img src="$Picture" alt="$Link" /></a>
                        <% else %>
							<img src="$Picture" alt="$Link" />
                        <% end_if %>
                    <% end_if %>

                    <% if not $Picture %>
                        <% if $Content %>
							<figcaption class="media-caption">
                                $Content
							</figcaption>
                        <% end_if %>
                    <% end_if %>
				</figure>
            <% end_loop %>
        <% end_if %>

		<div class="post-footer">
            <% if $Icon %><img src="$Icon" alt="$StatusType" class="post-icon" /> <% end_if %>

            <% if $CommentsDescriptor %>
				<span class="post-comment-count"><a href="$Link" target="_blank">$CommentsCount $CommentsDescriptor</a></span>
            <% end_if %>

            <% if $ReplyDescriptor %>
				<span class="post-replies-count"><a href="$Link" target="_blank">$ReplyCount $ReplyDescriptor</a></span>
            <% end_if %>

            <% if $LikesDescriptor %>
                <% if $canLikePost %>
                    <% if $_is == 'facebook' %>
                        <% include Facebook_LikeButton fbLink=$Link %>
                    <% else_if $_is == 'google-plus' %>
                        <% include Google_PlusOneButton gpLink=$Link %>
                    <% end_if %>
                <% else %>
	                <span class="post-likes-count"><a href="$Link" target="_blank">$LikesCount $LikesDescriptor</a></span>
                <% end_if %>
            <% end_if %>

            <% if $RetweetsDescriptor %>
				<span class="post-retweets-count">$Retweets $RetweetsDescriptor</span>
            <% end_if %>

            <% if $UserMentionsDescriptor %>
				<span class="post-mentions-count"><% if $UserMentions %>$UserMentions.Count<% else %>0<% end_if %> $UserMentionsDescriptor</span>

                <% if $canMentionAuthor %>
                    <% include Twitter_MentionButton twitterUser=$AuthorName %>
                <% end_if %>
            <% end_if %>

            <% if $ReshareCountDescriptor %>
				<span class="post-reshares-count"><a href="$Link" target="_blank">$ReshareCount $ReshareCountDescriptor</a></span>
            <% end_if %>
		</div>

        <% if $Comments %>
			<div class="post-comments">
                <% loop $Comments %>
					<div class="post-comment<% if $ReplyByPoster %> author-reply<% end_if %>">
                        <% if $Author %><h6>$Author <time>$Posted.Ago</time></h6> <% end_if %>
						<p>$Content</p>
					</div>
                <% end_loop %>
			</div>
        <% end_if %>
	</div>
</article>