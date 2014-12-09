<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Profile.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Profile extends DataObject {

    private static $singular_name = 'Profile';

    private static $db = [
        'Username' => 'Varchar',
        'Limit'    => 'Int',
        'Enabled'  => 'Boolean',
        'AddThis' => 'Varchar',
    ];

    private static $has_one = [
        'Parent' => 'SocialFeed',
    ];

    private static $defaults = [
        'Enabled' => true,
        'Limit'   => 5,
    ];

    private static $field_labels = [
        'UserID'                 => 'Username/Profile ID',
        'Enabled'                => 'Enabled (untick to hide from feed)',
        'Limit'                  => 'Show latest',
        'PlatformType'           => 'Platform',
        'APIKey'                 => 'API Key',
        'AllowPageLikes'         => 'Allow Facebook Page/Profile likes?',
        'AllowPostLikes'         => 'Allow post likes?',
        'AllowAuthorFollows'     => 'Allow Author follows?',
        'AllowAuthorMentions'    => 'Allow Author mentions?',
        'AllowHashTagTweets'     => 'Allow hash tag retweets? (May cause page to load slower if limit is higher than default)',
        'AllowGooglePlusFollows' => 'Allow Author follows?',
        'AllowPlusOnes'          => 'Allow post likes?',
    ];

    private static $summary_fields = [
        'Platform',
        'Username',
        'Enabled',
        'Limit',
    ];

    private static $templates = [
        'SocialFeed_Post',
    ];

    protected $provider = 'Milkyway\SS\SocialFeed\Providers\Models\HTTP';

    protected $environmentMapping = [
        'AddThis' => 'addthis_profile_id',
    ];

    protected $cachedEnvironmentMapping = [];

    public function canCreate($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if(get_class($this) == 'SocialFeed_Profile')
                    return false;

                if($this->Parent()->canCreate($member))
                    return true;
            }
        );

        return parent::canCreate($member);
    }

    public function canEdit($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent()->canEdit($member))
                    return true;
            }
        );

        return parent::canEdit($member);
    }

    public function canDelete($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent()->canDelete($member))
                    return true;
            }
        );

        return parent::canDelete($member);
    }

    public function canView($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent()->canView($member))
                    return true;
            }
        );

        return parent::canView($member);
    }

	public function getCMSFields() {
        $this->beforeExtending('updateCMSFields', function($fields) {
                $fields->removeByName('ParentID');
                $fields->removeByName('AddThis');

                $fields->addFieldsToTab('Root.Main', [
                        TextField::create('AddThis', _t('SocialFeed.ADDTHIS', 'Add This Profile'))
                            ->setDescription(_t('SocialFeed.DESC-ADDTHIS', 'AddThis Profile ID used for sharing (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)'))
                    ]);
            }
        );

        $this->afterExtending('updateCMSFields', function($fields) {
                $dataFields = $fields->dataFields();

                foreach($dataFields as $field) {
                    if($field instanceof \TextField)
                        $field->setAttribute('placeholder', $this->getValueFromServerEnvironment($field->Name));
                }
            }
        );

		return parent::getCMSFields();
	}

    public function getBetterButtonsUtils() {
        if($this->has_extension('BetterButtonDataObject')) {
            $extension = $this->getExtensionInstance('BetterButtonDataObject');
            $extension->setOwner($this);
            $utils   = $extension->getBetterButtonsUtils();
            $utils->removeByName('action_doNew');
        }
        else
            $utils = \FieldList::create();

        return $utils;
    }

    public function getTitle()
    {
        return $this->getValueFromEnvironment('Username') . ' (' . $this->i18n_singular_name() . ')';
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getFeedSettings() {
        return [
            'username' => $this->getValueFromEnvironment('Username'),
            'limit' => $this->Limit,
        ];
    }

    public function getPostSettings() {
        return [
            'AddThisProfileID' => $this->getValueFromEnvironment('AddThis'),
        ];
    }

    public function processPost(array $post, $postObject = null) {
        return $post;
    }

    public function getPlatform() {
        return $this->i18n_singular_name();
    }

    public function getTemplates() {
        return array_reverse($this->config()->templates);
    }

    public function getStyleClasses() {
        return \Milkyway\SS\Utilities::raw2htmlid('platform-' . str_replace('_', '-', strtolower($this->singular_name())));
    }

    public function Link() {
        return Controller::join_links($this->config()->url, $this->getValueFromEnvironment('Username'));
    }

    public function getValueFromEnvironment($setting, $cache = true) {
        if($this->$setting)
            return $this->$setting;
        elseif($this->Parent()->$setting)
            return $this->Parent()->$setting;

        return $this->getValueFromServerEnvironment($setting, $cache);
    }

    protected function getValueFromServerEnvironment($setting, $cache = true) {
        if(isset($this->environmentMapping[$setting])) {
            $envSetting = $this->environmentMapping[$setting];

            if($cache && isset($this->cachedEnvironmentMapping[$envSetting]))
                return $this->cachedEnvironmentMapping[$envSetting];

            $value = null;
            $prefix = get_class($this) == 'SocialFeed_Profile' ? '' : str_replace('SocialFeed_', '', get_class($this));

            if(SocialFeed::config()->$envSetting)
                $value = SocialFeed::config()->$envSetting;
            elseif($prefix && SiteConfig::current_site_config()->{$prefix.$setting})
                $value = SiteConfig::current_site_config()->{$prefix.$setting};
            elseif(!$prefix && SiteConfig::current_site_config()->{$setting})
                $value = SiteConfig::current_site_config()->{$setting};
            elseif(SiteConfig::config()->$envSetting)
                $value = SiteConfig::config()->$envSetting;
            elseif(getenv($envSetting))
                $value = getenv($envSetting);
            elseif(isset($_ENV[$envSetting]))
                $value = $_ENV[$envSetting];

            if($cache)
                $this->cachedEnvironmentMapping[$envSetting] = $value;

            return $value;
        }

        return null;
    }
}