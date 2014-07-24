<?php
/**
 * Milkyway Multimedia
 * SocialFeed.php
 *
 * @package social-feed
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 */

class SocialFeed extends Page {
	private static $description = 'A page that displays a social feed from various social platforms';

	private static $icon = 'social-feed/images/treeicons/social-feed.png';

    private static $db = [
        'CacheHours' => 'Int',
        'AddThis' => 'Varchar',
    ];

	private static $has_many = [
		'Profiles' => 'SocialFeed_Profile',
	];

    private static $defaults = [
        'CacheHours' => 6,
    ];

    protected $collection;

    public function __construct($record = null, $isSingleton = false, $model = null) {
        parent::__construct($record, $isSingleton, $model);

        $profiles = $this->Profiles()->exists() ? $this->Profiles()->filter('Enabled', 1) : $this->Profiles();
        $this->collection = Object::create('\Milkyway\SS\SocialFeed\Collector', $profiles);

        $this->beforeExtending('updateSettingsFields', function($fields) {
                $fields->addFieldToTab('Root', Tab::create(
                        'SocialPlatforms',
                        _t('SocialFeed.SOCIAL_PLATFORMS', 'Social Platforms'),
                        $gf = GridField::create(
                            'Profiles',
                            _t('SocialFeed.PROFILES', 'Profiles'),
                            $this->Profiles(),
                            $config = GridFieldConfig_RecordEditor::create()
                        ),
                        NumericField::create('CacheHours', _t('SocialFeed.CACHE', 'Cache for'))
                            ->setDescription(_t('SocialFeed.DESC-CACHE', 'Set how many hours the results from the various platforms are stored in cache for'))
                            ->setAttribute('placeholder', 6),
                        TextField::create('AddThis', _t('SocialFeed.ADDTHIS', 'Add This Profile'))
                            ->setDescription(_t('SocialFeed.DESC-ADDTHIS', 'AddThis Profile ID used for sharing (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)'))
                    )
                );

                $config
                    ->removeComponentsByType('GridFieldAddNewButton')
                    ->addComponent(new GridFieldAddNewMultiClass());

                if($columns = $config->getComponentByType('GridFieldDataColumns')) {
                    $displayColumns = $columns->getDisplayFields($gf);

                    if(isset($displayColumns['Enabled'])) {
                        $displayColumns['Enabled'] = _t('SHOW', 'Show');
                        $columns->setDisplayFields($displayColumns);

                        $columns->setFieldFormatting([
                                'Enabled' => function($value, $record) {
                                        return $value ? '<span class="ui-button-icon-primary ui-icon btn-icon-accept boolean-yes"></span>' : '<span class="ui-button-icon-primary ui-icon btn-icon-decline boolean-no"></span>';
                                    }
                            ]);
                    }
                }
            }
        );
    }

    public function getFeed() {
        return $this->collection->all();
    }
}