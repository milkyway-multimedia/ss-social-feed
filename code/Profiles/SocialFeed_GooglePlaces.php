<?php

/**
 * Milkyway Multimedia
 * SocialFeed_GooglePlaces.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_GooglePlaces extends SocialFeed_Profile {
    private static $url = 'http://+.google.com';

    private static $singular_name = 'Google Places';

	private static $db = [
		'ApiKey'                 => 'Varchar',
	    'LocationName'               => 'Text',
	    'LocationAddress'               => 'Text',
	    'OnlyShowIfRatingIsHigherThan' => "Enum('4,3,2,1,0','2')",
	];

	private static $db_to_environment_mapping = [
		'ApiKey' => 'GooglePlaces|GooglePlus|SocialFeed|SiteConfig.googleplaces_api_key',
	];

	protected $provider = 'Milkyway\SS\SocialFeed\Providers\GooglePlaces';

	public function getCMSFields() {
		$this->beforeExtending(
			'updateCMSFields',
			function ($fields)
			{
				$fields->removeByName('LocationName');
				$fields->removeByName('LocationAddress');

				if(\ClassInfo::exists('Select2Field')) {
					$fields->replaceField('Username', $location = \Select2Field::create('LocationAndUsername', _t('GooglePlaces.LOCATION', 'Location'), '', function ($query = '', $limit = 10) {
						return $query ? \Object::create($this->Provider, 0, (array)$this->OauthConfiguration)->textSearch($query, [
							'query' => [
								'key' => $this->setting('ApiKey'),
							]
						]) : [];
					}, null, 'name||formatted_address', 'place_id||name||formatted_address')
						->setPrefetch(false)
						->requireSelection(true)
						->setMinimumSearchLength(15)
					);

					if ($this->LocationName) {
						$location->setEmptyString($this->LocationName . ' - ' . $this->LocationAddress);
					}
				}
				else if($username = $fields->dataFieldByName('Username')) {
					$username->setTitle(_t('SocialFeed.PLACE_ID', 'Place ID'));
					$username->setDescription(_t('SocialFeed.DESC-PLACE_ID', '<a href="{url}" target="_blank">You can find the place ID of your location here.</a>', [
						'url' => 'https://developers.google.com/places/documentation/place-id#find-id',
					]));
				}
			}
		);

		return parent::getCMSFields();
	}

	public function getTitle() {
		return $this->LocationName ? $this->LocationName . ' (' . $this->i18n_singular_name() . ')' : parent::getTitle();
	}

	public function saveLocationAndUsername($value) {
		if($value && ($value != $this->Username . '||' . $this->LocationName . '||' . $this->LocationAddress || $value != $this->LocationName . ' - ' . $this->LocationAddress)) {
			$values = explode('||', $value);

			$this->Username = $values[0];
			$this->LocationName = $values[1];
			$this->LocationAddress = $values[2];
		}
	}

	public function setting($setting, $parent = null, $cache = true) {
		$value = parent::setting($setting, $parent, $cache);

		if($setting == 'ApiKey' && !$value) {
			$value = singleton('SocialFeed_GooglePlus')->setting($setting, $parent, $cache);
			singleton('env')->set('GooglePlaces|GooglePlus|SocialFeed|SiteConfig.googleplaces_api_key', $value);
		}

		return $value;
	}

	public function getFeedSettings($parent = null) {
		return array_merge(parent::getFeedSettings(), [
				'query' => [
					'key' => $this->setting('ApiKey', $parent),
					'placeid' => $this->setting('Username'),
				],
			]
		);
	}

	public function processPost(array $post, $postObject = null) {
		$post = parent::processPost($post, $postObject);

		if(isset($post['Rating']) && $post['Rating'] <= $this->OnlyShowIfRatingIsHigherThan)
			$post['hidden'] = true;

		return $post;
	}

	protected function detailsForPlatform() {
		$details = parent::detailsForPlatform();

		if(isset($details[$this->fieldLabel('Username')]))
			unset($details[$this->fieldLabel('Username')]);

		return [
			$this->fieldLabel('Location') => $this->LocationName,
			$this->fieldLabel('Address') => $this->LocationAddress,
			$this->fieldLabel('PlaceId') => $this->Username,
		];
	}
} 