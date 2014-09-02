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

    protected $provider = 'Milkyway\SS\SocialFeed\Providers\GooglePlus';

    private static $db = array(
        'ApiKey'                 => 'Varchar',

        'AllowGooglePlusFollows' => 'Boolean',
        'AllowPlusOnes'          => 'Boolean',
    );

    protected $environmentMapping = [
        'ApiKey'              => 'googleplus_api_key',
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

    public static function google_plus_follow_shortcode($arguments, $content = null, $parser = null, $template = 'Google_FollowButton') {
        $link = isset($arguments['link']) ? $arguments['link'] : $content;
        $user = $content;

        if($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            $link = '';
            $user = $link;
        }

        return \ArrayData::create(array_merge(
                array(
                    'gpLink' => $link,
                    'gpUser' => $user,
                    'gpAnnotation' => isset($arguments['annotation']) ? $arguments['annotation'] : null,
                    'gpSize' => isset($arguments['size']) ? $arguments['size'] : null,
                ), $arguments)
        )->renderWith($template);
    }

    public static function google_plus_one_shortcode($arguments, $content = null, $parser = null) {
        return static::google_plus_follow_shortcode($arguments, $content, $parser, 'Google_PlusOneButton');
    }
} 