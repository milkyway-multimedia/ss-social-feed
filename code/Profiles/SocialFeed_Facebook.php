<?php /**
 * Milkyway Multimedia
 * SocialFeed_Facebook.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Facebook extends SocialFeed_Profile {
    private static $singular_name = 'Facebook Page';

    private static $db = array(
        'ConsumerKey' => 'Varchar',
        'ConsumerSecret' => 'Varchar',

        'Author' => 'Varchar',
        'AuthorOnly' => 'Boolean',
        'AllowPageLikes' => 'Boolean',
        'AllowPostLikes' => 'Boolean',
    );
} 