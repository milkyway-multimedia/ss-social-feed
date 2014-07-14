<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Twitter.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Twitter extends SocialFeed_Profile {

    private static $url = 'http://twitter.com';

    private static $singular_name = 'Twitter';

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

        $this->beforeExtending(
            'updateCMSFields',
            function ($fields)
            {
                if ($type = $fields->dataFieldByName('Type'))
                {
                    $type->setSource([
                            'user_timeline'     => _t('SocialFeed_Twitter.USER_TIMELINE', 'User Tweets'),
                            'mentions_timeline' => _t('SocialFeed_Twitter.MENTIONS_TIMELINE', 'User Mentions'),
                            'home_timeline'     => _t('SocialFeed_Twitter.HOME_TIMELINE', 'Home Timeline'),
                        ]
                    );
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

    public function getFeedSettings() {
        return array_merge(parent::getFeedSettings(), [
                'type' => 'statuses/' . $this->Type,
                'query' => [
                    'screen_name' => $this->getValueFromEnvironment('Username'),
                    'count' => $this->Limit,
                    'contributor_details' => true,
                ],
            ]
        );
    }

    public function getPostSettings() {
        return array_merge(parent::getPostSettings(), [
                'canLikePage' => $this->AllowAuthorFollows,
                'canLikePost' => $this->AllowAuthorMentions,
            ]
        );
    }

    public function processPost(array $post) {
        $post = parent::processPost($post);

        if(isset($post['HashTags']) && isset($post['Content'])) {
            foreach($post['HashTags'] as $tag) {
                if(!isset($tag['Content'])) continue;

                if($this->AllowHashTagTweets) {
                    \Milkyway\SocialFeed\Utilities::require_twitter_script();
                    $post['Content'] = str_replace('#' . $post['Content'], sprintf('<span class="twitter-btn"><a href="https://twitter.com/intent/tweet?button_hashtag=%s" class="twitter-hashtag-button" target="_blank">#%s</a></span>', $tag['Content'], $tag['Content']), $post['Content']);
                }
                else
                    $post['Content'] = str_replace('#' . $post['Content'], sprintf('<a href="%s" target="_blank">#%s</a>', 'http://twitter.com/search?q=' . urlencode('#' . $tag['Content']) . '&src=hash', $tag['Content']), $post['Content']);
            }
        }

        return $post;
    }

    public function LikeButton() {
        return $this->customise(['twitterUser' => $this->getValueFromEnvironment('Username')])->renderWith('Twitter_FollowButton');
    }

    public function LikePostButton() {
        return $this->customise(['twitterUser' => $this->getValueFromEnvironment('Username')])->renderWith('Twitter_MentionButton');
    }
} 