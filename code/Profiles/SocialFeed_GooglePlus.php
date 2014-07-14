<?php

/**
 * Milkyway Multimedia
 * SocialFeed_GooglePlus.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_GooglePlus extends SocialFeed_Profile {
    private static $url = 'http://plus.google.com';

    private static $singular_name = 'Google Plus';

    protected $provider = 'Milkyway\SocialFeed\Providers\GooglePlus';

    private static $db = array(
        'ApiKey'                 => 'Varchar',

        'AllowGooglePlusFollows' => 'Boolean',
        'AllowPlusOnes'          => 'Boolean',
    );

    protected $environmentMapping = [
        'ApiKey'              => 'google+_api_key',
    ];

    public function getFeedSettings() {
        return array_merge(parent::getFeedSettings(), [
                'query' => [
                    'key' => $this->getValueFromEnvironment('ApiKey'),
                    'maxResults' => $this->Limit,
                ],
            ]
        );
    }

    public function getPostSettings() {
        return [
            'canLikePage' => $this->AllowGooglePlusFollows,
            'canLikePost' => $this->AllowPlusOnes,
        ];
    }

    public function LikeButton($url = '') {
        if(!$url)
            $url = $this->Link();

        return $this->customise(['gpLink' => $url])->renderWith('Google_FollowButton');
    }

    public function LikePostButton($url = '') {
        if(!$url) return '';
        return $this->customise(['gpLink' => $url])->renderWith('Google_PlusOneButton');
    }
} 