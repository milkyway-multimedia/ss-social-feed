<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Twitter.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Twitter extends SocialFeed_Profile {

    private static $singular_name = 'Twitter Username';

    private static $db = [
        'Type'                => 'Enum(array("user_timeline","mentions_timeline","home_timeline"))',

        'ApiKey'              => 'Varchar',
        'ApiSecret'           => 'Varchar',
        'AccessToken'         => 'Varchar',
        'AccessTokenSecret'   => 'Varchar',

        'IncludeReplies'      => 'Boolean',
        'AllowAuthorFollows'  => 'Boolean',
        'AllowAuthorMentions' => 'Boolean',
        'AllowHashTagTweets'  => 'Boolean',
    ];

    protected $provider = 'Milkyway\SocialFeed\Providers\Twitter';

    protected $environmentMapping = [
        'ApiKey'              => 'twitter_api_key',
        'ApiSecret'           => 'twitter_api_secret',
        'AccessToken'         => 'twitter_access_token',
        'AccessTokenSecret'   => 'twitter_access_token_secret',
    ];

    public function __construct($record = null, $isSingleton = false, $model = null)
    {
        parent::__construct($record, $isSingleton, $model);

        $types = [
            'user_timeline'     => _t('SocialFeed_Twitter.USER_TIMELINE', 'User Tweets'),
            'mentions_timeline' => _t('SocialFeed_Twitter.MENTIONS_TIMELINE', 'User Mentions'),
            'home_timeline'     => _t('SocialFeed_Twitter.HOME_TIMELINE', 'Home Timeline'),
        ];

        $this->beforeExtending(
            'updateCMSFields',
            function ($fields) use ($types)
            {
                if ($type = $fields->dataFieldByName('Type'))
                {
                    $type->setSource($types);
                }
            }
        );
    }

    public function getOauthConfiguration()
    {
        return [
            'consumer_key'    => $this->getValueFromEnvironment('ApiKey'),
            'consumer_secret' => $this->getValueFromEnvironment('ApiSecret'),
            'token'           => $this->getValueFromEnvironment('AccessToken'),
            'token_secret'    => $this->getValueFromEnvironment('AccessTokenSecret'),
        ];
    }

    public function getPostSettings() {
        return [
            'canLikePage' => $this->AllowAuthorFollows,
        ];
    }
} 