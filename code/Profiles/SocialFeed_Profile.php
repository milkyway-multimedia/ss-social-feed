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

    protected $provider = 'Milkyway\SocialFeed\Providers\Models\HTTP';

    protected $environmentMapping = [
        'AddThis' => 'addthis_profile_id',
    ];

    protected $cachedEnvironmentMapping = [];

    public function __construct($record = null, $isSingleton = false, $model = null) {
        parent::__construct($record, $isSingleton, $model);

        $this->beforeExtending('canCreate', function($member = null) {
                if(get_class($this) == 'SocialFeed_Profile')
                    return false;
            }
        );

        $this->beforeExtending('updateCMSFields', function($fields) {
                $fields->removeByName('ParentID');
                $fields->removeByName('AddThis');

                $fields->addFieldsToTab('Root.Main', [
                    TextField::create('AddThis', _t('SocialFeed.ADDTHIS', 'Add This Profile'))
                    ->setDescription(_t('SocialFeed.DESC-ADDTHIS', 'AddThis Profile ID used for sharing (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)'))
                ]);
            }
        );
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

    public function processPost(array $post) {
        return $post;
    }

    public function getPlatform() {
        return $this->i18n_singular_name();
    }

    public function getTemplates() {
        return array_reverse($this->config()->templates);
    }

    public function getStyleClasses() {
        return \Milkyway\Utilities::raw2htmlid('platform-' . str_replace('_', '-', strtolower($this->singular_name())));
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
            $setting = $this->environmentMapping[$setting];

            if($cache && isset($this->cachedEnvironmentMapping[$setting]))
                return $this->cachedEnvironmentMapping[$setting];

            $value = null;

            if(SocialFeed::config()->$setting)
                $value = SocialFeed::config()->$setting;
            elseif(SiteConfig::config()->$setting)
                $value = SiteConfig::config()->$setting;
            elseif(getenv($setting))
                $value = getenv($setting);
            elseif(isset($_ENV[$setting]))
                $value = $_ENV[$setting];

            if($cache)
                $this->cachedEnvironmentMapping[$setting] = $value;

            return $value;
        }

        return null;
    }
}