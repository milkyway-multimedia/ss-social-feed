<?php /**
 * Milkyway Multimedia
 * SocialFeed_Twitter.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Twitter extends SocialFeed_Profile {
    private static $singular_name = 'Twitter Username';

    private static $db = array(
        'ConsumerKey' => 'Varchar',
        'ConsumerSecret' => 'Varchar',

        'Token' => 'Varchar',
        'TokenSecret' => 'Varchar',

        'IncludeReplies' => 'Boolean',
        'AllowAuthorFollows' => 'Boolean',
        'AllowAuthorMentions' => 'Boolean',
        'AllowHashTagTweets' => 'Boolean',
    );
} 