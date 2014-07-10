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

	private static $has_many = array(
		'Profiles' => 'SocialFeed_Profile',
	);

    protected $collection;

    public function __construct($record = null, $isSingleton = false, $model = null) {
        parent::__construct($record, $isSingleton, $model);

        $profiles = $this->Profiles()->exists() ? $this->Profiles()->filter('Enabled', 1) : $this->Profiles();
        $this->collection = Object::create('\Milkyway\SocialFeed\Collector', $profiles);

        $this->beforeExtending('updateSettingsFields', function($fields) {
                $fields->addFieldToTab('Root', Tab::create(
                        'SocialPlatforms',
                        _t('SocialFeed.SOCIAL_PLATFORMS', 'Social Platforms'),
                        $gf = GridField::create(
                            'Profiles',
                            _t('SocialFeed.PROFILES', 'Profiles'),
                            $this->Profiles(),
                            $config = GridFieldConfig_RecordEditor::create()
                        )
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

                        $columns->setFieldFormatting(array(
                                'Enabled' => function($value, $record) {
                                        return $value ? '<span class="ui-button-icon-primary ui-icon btn-icon-accept boolean-yes"></span>' : '<span class="ui-button-icon-primary ui-icon btn-icon-decline boolean-no"></span>';
                                    }
                            ));
                    }
                }
            }
        );
    }

    public function getFeed() {
        return $this->collection->all();
    }
}