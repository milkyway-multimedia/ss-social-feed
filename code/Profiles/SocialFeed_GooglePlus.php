<?php

/**
 * Milkyway Multimedia
 * SocialFeed_GooglePlus.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_GooglePlus extends SocialFeed_Profile {
    private static $url = 'http://plus.google.com';

    private static $singular_name = 'Google Plus';

    private static $db = array(
        'ApiKey'                 => 'Varchar',

        'AllowGooglePlusFollows' => 'Boolean',
        'AllowPlusOnes'          => 'Boolean',
    );

	private static $db_to_environment_mapping = [
		'ApiKey' => 'GooglePlus|SocialFeed|SiteConfig.googleplus_api_key',
	];

	protected $provider = 'Milkyway\SS\SocialFeed\Providers\GooglePlus';

    public function getFeedSettings($parent = null) {
        return array_merge(parent::getFeedSettings(), [
                'query' => [
                    'key' => $this->setting('ApiKey', $parent),
                    'maxResults' => $this->Limit,
                ],
            ]
        );
    }

    public function getPostSettings($parent = null) {
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