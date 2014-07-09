<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Profile.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Profile extends DataObject {

    private static $singular_name = 'Profile';

    private static $db = array(
        'Username' => 'Varchar',
        'Limit'    => 'Int',
        'Enabled'  => 'Boolean',
    );

    private static $has_one = array(
        'Parent' => 'SocialFeed',
    );

    private static $defaults = array(
        'Enabled' => true,
        'Limit'   => 5,
    );

    private static $field_labels = array(
        'UserID'                 => 'Username/Profile ID',
        'Enabled'                => 'Enabled (untick to hide from feed)',
        'Limit'                  => 'Show latest',
        'PlatformType'           => 'Platform',
        'APIKey'                 => 'API Key',
        'AllowPageLikes'         => 'Allow Facebook Page/Profile likes?',
        'AllowPostLikes'         => 'Allow post likes?',
        'AllowAuthorFollows'     => 'Allow Author follows?',
        'AllowAuthorMentions'    => 'Allow Author mentions?',
        'AllowHashTagTweets'     => 'Allow hash tag retweets? (May cause page to load slower if limit is higher than default)',
        'AllowGooglePlusFollows' => 'Allow Author follows?',
        'AllowPlusOnes'          => 'Allow post likes?',
    );

    private static $summary_fields = array(
        'Username',
        'Enabled',
        'Limit',
    );

    public function __construct($record = null, $isSingleton = false, $model = null) {
        parent::__construct($record, $isSingleton, $model);

        $this->beforeExtending('canCreate', function($member = null) {
                if(get_class($this) == 'SocialFeed_Profile')
                    return false;
            }
        );
    }

    public function getTitle()
    {
        return $this->Username . ' (' . $this->i18n_singular_name() . ')';
    }
}