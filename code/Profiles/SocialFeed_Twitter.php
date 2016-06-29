<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Twitter.php
 *
 * @package milkyway-multimedia/ss-social-feed
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
        'AllowFavourites'     => 'Boolean',
        'AllowRetweets'       => 'Boolean',
        'AllowAuthorFollows'  => 'Boolean',
        'AllowAuthorMentions' => 'Boolean',
        'AllowHashTagTweets'  => 'Boolean',
    ];

	private static $db_to_environment_mapping = [
		'ApiKey' => 'Twitter|SocialFeed|SiteConfig.twitter_api_key',
		'ApiSecret' => 'Twitter|SocialFeed|SiteConfig.twitter_api_secret',
		'AccessToken' => 'Twitter|SocialFeed|SiteConfig.twitter_access_token',
		'AccessTokenSecret' => 'Twitter|SocialFeed|SiteConfig.twitter_access_token_secret',
	];

    protected $provider = 'Milkyway\SS\SocialFeed\Providers\Twitter';

	public function getCMSFields() {
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

		return parent::getCMSFields();
	}

    public function getProviderConfiguration()
    {
        return [
            'consumer_key'    => $this->setting('ApiKey'),
            'consumer_secret' => $this->setting('ApiSecret'),
            'token'           => $this->setting('AccessToken'),
            'token_secret'    => $this->setting('AccessTokenSecret'),
        ];
    }

    public function getFeedSettings($parent = null) {
        return array_merge(parent::getFeedSettings($parent), [
                'type' => 'statuses/' . $this->Type,
                'query' => [
                    'screen_name' => $this->setting('Username', $parent),
                    'count' => $this->Limit,
                    'contributor_details' => true,
                ],
            ]
        );
    }

    public function getPostSettings($parent = null) {
        return array_merge(parent::getPostSettings($parent), [
                'canLikePage' => $this->AllowAuthorFollows,
                'canLikePost' => $this->AllowFavourites,
            ]
        );
    }

    public function processPost(array $post, $postObject = null) {
        $post = parent::processPost($post, $postObject);

        if(isset($post['HashTags']) && isset($post['Content'])) {
            foreach($post['HashTags'] as $tag) {
                if(!isset($tag['Content'])) continue;

                if($this->AllowHashTagTweets) {
                    \Milkyway\SS\SocialFeed\Utilities::require_twitter_script();
                    $post['Content'] = str_replace('#' . $post['Content'], sprintf('<span class="twitter-btn"><a href="https://twitter.com/intent/tweet?button_hashtag=%s" class="twitter-hashtag-button" target="_blank">#%s</a></span>', $tag['Content'], $tag['Content']), $post['Content']);
                }
                else
                    $post['Content'] = str_replace('#' . $post['Content'], sprintf('<a href="%s" target="_blank">#%s</a>', 'http://twitter.com/search?q=' . urlencode('#' . $tag['Content']) . '&src=hash', $tag['Content']), $post['Content']);
            }
        }

        return $post;
    }

    public function LikeButton() {
        return $this->customise(['twitterUser' => $this->setting('Username')])->renderWith('Twitter_FollowButton');
    }

    public function LikePostButton($link = '', $id = '', $likes = 0, $likesDescriptor = '') {
        return $this->customise([
            'tweetId' => $id,
            'Favourites' => $likes,
            'FavouritesDescriptor' => $likesDescriptor,
        ])->renderWith('Twitter_FavouriteButton');
    }

    public function RetweetButton($id, $retweets = 0, $retweetsDescriptor = '') {
        return $this->customise([
            'tweetId' => $id,
            'Retweets' =>$retweets,
            'RetweetsDescriptor' => $retweetsDescriptor,
        ])->renderWith('Twitter_RetweetButton');
    }

    public function MentionButton() {
        return $this->customise([
            'twitterUser' => $this->setting('Username'),
        ])->renderWith('Twitter_MentionButton');
    }

	protected function detailsForPlatform() {
		return array_merge(parent::detailsForPlatform(), [
			$this->fieldLabel('Type') => $this->Type,
		]);
	}
}