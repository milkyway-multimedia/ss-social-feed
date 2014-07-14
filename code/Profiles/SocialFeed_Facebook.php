<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Facebook.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Facebook extends SocialFeed_Profile {
    private static $url = 'http://facebook.com';

    private static $singular_name = 'Facebook Page';

    private static $db = array(
        'AppID'          => 'Varchar',
        'AppSecret'      => 'Varchar',
        'Author'         => 'Varchar',
        'AuthorOnly'     => 'Boolean',
        'AllowPageLikes' => 'Boolean',
        'AllowPostLikes' => 'Boolean',
    );

    protected $provider = 'Milkyway\SocialFeed\Providers\Facebook';

    protected $environmentMapping = [
        'AppID'     => 'facebook_application_id',
        'AppSecret' => 'facebook_application_secret',
    ];

    public function getOauthConfiguration()
    {
        return [
            'consumer_key'    => $this->getValueFromEnvironment('AppID'),
            'consumer_secret' => $this->getValueFromEnvironment('AppSecret'),
        ];
    }

    public function getFeedSettings() {
        return array_merge(parent::getFeedSettings(), [
                'query' => [
                    'access_token' => $this->getValueFromEnvironment('AppID') . '|' . $this->getValueFromEnvironment('AppSecret'),
                    'limit' => $this->Limit,
                ],
            ]
        );
    }

    public function getPostSettings() {
        return array_merge(parent::getPostSettings(), [
            'canLikePage' => $this->AllowPageLikes,
            'canLikePost' => $this->AllowPostLikes,
        ]);
    }

    public function LikeButton($url = '') {
        if(!$url)
            $url = $this->Link();

        return $this->customise(['fbLink' => $url])->renderWith('Facebook_LikeButton');
    }

    public function LikePostButton($url = '') {
        return $this->LikeButton($url);
    }
} 