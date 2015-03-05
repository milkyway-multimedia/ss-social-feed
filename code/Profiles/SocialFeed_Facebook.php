<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Facebook.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Facebook extends SocialFeed_Profile
{
	private static $url = 'http://facebook.com';

	private static $singular_name = 'Facebook';

	private static $db = array(
		'Type' => 'Enum(array("feed","albums","events","links","milestones","offers","photos","photos/uploaded","ratings","statuses","videos","videos/uploaded"))',

		'AppID' => 'Varchar',
		'AppSecret' => 'Varchar',
		'Author' => 'Varchar',
		'AuthorOnly' => 'Boolean',
		'AllowPageLikes' => 'Boolean',
		'AllowPostLikes' => 'Boolean',
		'AllowPostShare' => 'Boolean',
		'AllowPostSend' => 'Boolean',
		'AllowHashTagLinks'  => 'Boolean',
	    'OnlyShowIfRatingIsHigherThan' => "Enum('4,3,2,1,0','2')",
	);

	private static $defaults = array(
		'OnlyShowIfRatingIsHigherThan' => 2,
	);

	private static $db_to_environment_mapping = [
		'AppID' => 'Facebook|SocialFeed|SiteConfig.facebook_application_id',
		'AppSecret' => 'Facebook|SocialFeed|SiteConfig.facebook_application_secret',
	];

	protected $provider = 'Milkyway\SS\SocialFeed\Providers\Facebook';

	public function getCMSFields()
	{
		$this->beforeExtending(
			'updateCMSFields',
			function ($fields) {
				if ($type = $fields->dataFieldByName('Type')) {
					$types = [
						'feed' => _t('SocialFeed_Facebook.FEED', 'News feed (inc. statuses/posts, milestones and links)'),
						'albums' => _t('SocialFeed_Facebook.ALBUMS', 'Albums'),
						'events' => _t('SocialFeed_Facebook.EVENTS', 'Events'),
						'links' => _t('SocialFeed_Facebook.LINKS', 'Posted links'),
						'milestones' => _t('SocialFeed_Facebook.MILESTONES', 'Milestones'),
						'offers' => _t('SocialFeed_Facebook.OFFERS', 'Offers'),
						'photos' => _t('SocialFeed_Facebook.PHOTOS_TAGGED_IN', 'Photos where I have been tagged'),
						'photos/uploaded' => _t('SocialFeed_Facebook.UPLOADED_PHOTOS', 'Uploaded photos'),
						'videos' => _t('SocialFeed_Facebook.VIDEOS_TAGGED_IN', 'Videos where I have been tagged'),
						'videos/uploaded' => _t('SocialFeed_Facebook.VIDEOS_UPLOADED_PHOTOS', 'Uploaded videos'),
					];

					if(class_exists('League\OAuth2\Client\Token\AccessToken'))
						$types['ratings'] = _t('SocialFeed_Facebook.RATINGS', 'Reviews received (requires Facebook login permissions)');

					$type->setSource($types);
				}
			}
		);

		return parent::getCMSFields();
	}

	public function getTitle()
	{
		return parent::getTitle() . ' - ' . $this->Type;
	}

	public function getOauthConfiguration()
	{
		return [
			'consumer_key' => $this->setting('AppID'),
			'consumer_secret' => $this->setting('AppSecret'),
		];
	}

	public function getRequiresExtendedPermissions() {
		switch ($this->Type) {
			case 'ratings':
				return ['manage_pages'];
			default:
				return [];
		}
	}

	public function getFeedSettings($parent = null)
	{
		$settings = [];

		if($this->Type == 'events')
			$settings['since'] = '2004-04-02'; // Get all events since the founding on Facebook!

		return array_merge(parent::getFeedSettings($parent), [
				'type' => $this->Type,
				'query' => array_merge([
					'access_token' => $this->setting('AppID', $parent) . '|' . $this->setting('AppSecret', $parent),
					'limit' => $this->Limit,
				], $settings),
			]
		);
	}

	public function getPostSettings($parent = null)
	{
		return array_merge(parent::getPostSettings($parent), [
			'canLikePage' => $this->AllowPageLikes,
			'canLikePost' => $this->AllowPostLikes,
		]);
	}

	public function processPost(array $post, $postObject = null) {
		$post = parent::processPost($post, $postObject);

		if($this->AllowHashTagLinks) {
			if($post['Content'])
				$post['Content'] = $this->addHashTags($post['Content']);

			if($post['Description'])
				$post['Description'] = $this->addHashTags($post['Description']);
		}

		if(isset($post['Rating']) && $post['Rating'] <= $this->OnlyShowIfRatingIsHigherThan)
			$post['hidden'] = true;

		return $post;
	}

	public function LikeButton($url = '')
	{
		if (!$url)
			$url = $this->Link();

		return $this->customise(['fbLink' => $url])->renderWith('Facebook_LikeButton');
	}

	public function LikePostButton($url = '')
	{
		return $this->LikeButton($url);
	}

	protected function detailsForPlatform() {
		return array_merge(parent::detailsForPlatform(), [
			$this->fieldLabel('Type') => $this->Type,
		]);
	}

	protected function addHashTags($content) {
		return preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', str_replace('{url}', \Controller::join_links($this->url, 'hashtag') . '/', '\1#<a href="{url}\2" target="\_blank">\2</a>'), $content);
	}
} 