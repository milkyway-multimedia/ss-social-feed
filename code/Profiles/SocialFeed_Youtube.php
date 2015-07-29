<?php

/**
 * Milkyway Multimedia
 * SocialFeed_GooglePlus.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Youtube extends SocialFeed_Profile {
    private static $url = 'http://youtube.com';

    private static $singular_name = 'Youtube';

    private static $db = array(
        'ApiKey'                 => 'Varchar',
        'ChannelId'              => 'Varchar',

        'AllowUserSubscribes'    => 'Boolean',
    );

	private static $db_to_environment_mapping = [
		'ApiKey' => 'GooglePlus|SocialFeed|SiteConfig.youtube_api_key',
	];

	protected $provider = 'Milkyway\SS\SocialFeed\Providers\Youtube';

    public function getCMSFields() {
        $this->beforeExtending(
            'updateCMSFields',
            function ($fields)
            {
                $fields->removeByName('ChannelId');

                $fields->insertBefore(\TextField::create('ChannelId', $this->fieldLabel('ChannelId'))->setDescription(_t('SocialFeed.DESC-CHANNEL_ID', 'If left empty, will use the channel of the username below')), 'Username');
            }
        );

        return parent::getCMSFields();
    }

    public function getFeedSettings($parent = null) {
        return array_merge(parent::getFeedSettings(), [
                'query' => [
                    'key' => $this->setting('ApiKey', $parent),
                    'channelId' => $this->ChannelId,
                    'order' => 'date',
                    'part' => 'snippet',
                    'maxResults' => $this->Limit,
                ],
            ]
        );
    }

    public function getPostSettings($parent = null) {
        return [
            'canLikePage' => $this->AllowUserSubscribes,
        ];
    }

    public function setting($setting, $parent = null, $cache = true) {
        $value = parent::setting($setting, $parent, $cache);

        if($setting == 'ApiKey' && !$value) {
            $value = singleton('SocialFeed_GooglePlaces')->setting($setting, $parent, $cache);
            singleton('env')->set('GooglePlaces|GooglePlus|SocialFeed|SiteConfig.youtube_api_key', $value);
        }

        return $value;
    }

    public function LikeButton($channelLink = '', $channelId = '') {
        if(!$channelId)
            $channelId = $this->ChannelId;

        return $channelId ?
            $this->customise(['youtubeChannelId' => $channelId])->renderWith('Youtube_SubscribeButton')
            : '';
    }

    public function saveUsername($value) {
        if(!$this->ChannelId && $this->Username != $value) {
            $ids = \Object::create($this->Provider, 0, (array)$this->OauthConfiguration)->channelSearch($value, [
                'query' => [
                    'key' => $this->setting('ApiKey'),
                ]
            ]);

            if(count($ids) && isset($ids[0]) && isset($ids[0]['id'])) {
                $this->ChannelId = $ids[0]['id'];
            }
        }

        $this->Username = $value;
    }

    public function validate() {
        $this->beforeExtending(
            'validate',
            function ($result)
            {
                if(!$this->ChannelId) {
                    $result->error(_t('SocialFeed.DESC-YOUTUBE-NO_CHANNEL', 'No channel set, please check the username or channel id you entered'));
                }
            }
        );

        return parent::validate();
    }
} 