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
	);

	protected $provider = 'Milkyway\SS\SocialFeed\Providers\Facebook';

	protected $environmentMapping = [
		'AppID' => 'facebook_application_id',
		'AppSecret' => 'facebook_application_secret',
	];

	public function getCMSFields()
	{
		$this->beforeExtending(
			'updateCMSFields',
			function ($fields) {
				if ($type = $fields->dataFieldByName('Type')) {
					$type->setSource([
							'feed' => _t('SocialFeed_Facebook.FEED', 'News feed (inc. statuses/posts, milestones and links)'),
							'albums' => _t('SocialFeed_Facebook.ALBUMS', 'Albums'),
							'events' => _t('SocialFeed_Facebook.EVENTS', 'Events'),
							'links' => _t('SocialFeed_Facebook.LINKS', 'Posted links'),
							'milestones' => _t('SocialFeed_Facebook.MILESTONES', 'Milestones'),
							'offers' => _t('SocialFeed_Facebook.OFFERS', 'Offers'),
							'photos' => _t('SocialFeed_Facebook.PHOTOS_TAGGED_IN', 'Photos where I have been tagged'),
							'photos/uploaded' => _t('SocialFeed_Facebook.UPLOADED_PHOTOS', 'Uploaded photos'),
							//'ratings' => _t('SocialFeed_Facebook.RATINGS', 'Reviews received'),
							//'statuses' => _t('SocialFeed_Facebook.STATUSES', 'Statuses/Posts'),
							'videos' => _t('SocialFeed_Facebook.VIDEOS_TAGGED_IN', 'Videos where I have been tagged'),
							'videos/uploaded' => _t('SocialFeed_Facebook.VIDEOS_UPLOADED_PHOTOS', 'Uploaded videos'),
						]
					);
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
			'consumer_key' => $this->getValueFromEnvironment('AppID'),
			'consumer_secret' => $this->getValueFromEnvironment('AppSecret'),
		];
	}

	public function getFeedSettings()
	{
		$settings = [];

		if($this->Type == 'events')
			$settings['since'] = '2004-04-02'; // Get all events since the founding on Facebook!

		return array_merge(parent::getFeedSettings(), [
				'type' => $this->Type,
				'query' => array_merge([
					'access_token' => $this->getValueFromEnvironment('AppID') . '|' . $this->getValueFromEnvironment('AppSecret'),
					'limit' => $this->Limit,
				], $settings),
			]
		);
	}

	public function getPostSettings()
	{
		return array_merge(parent::getPostSettings(), [
			'canLikePage' => $this->AllowPageLikes,
			'canLikePost' => $this->AllowPostLikes,
		]);
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

	protected function provideDetailsForPlatform() {
		$this->beforeExtending('updatePlatformDetails', function(&$details) {
			$details[$this->fieldLabel('Type')] = $this->Type;
		});

		return parent::provideDetailsForPlatform();
	}
} 