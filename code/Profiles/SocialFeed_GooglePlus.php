<?php

/**
 * Milkyway Multimedia
 * SocialFeed_GooglePlus.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_GooglePlus extends SocialFeed_Profile {
    private static $singular_name = 'Google Plus Page';

    private static $db = array(
        'APIKey'                 => 'Varchar',
        'AllowGooglePlusFollows' => 'Boolean',
        'AllowPlusOnes'          => 'Boolean',
    );
} 