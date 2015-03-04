<?php
/**
 * Created by PhpStorm.
 * User: mwm-15r
 * Date: 22/12/2014
 * Time: 6:04 PM
 */

namespace Milkyway\SS\SocialFeed\Extensions;

class HasProfiles extends \DataExtension {
	protected static $connected_relations = [];

    private static $db = [
        'SocialFeed_Limit' => 'Int',
        'CacheHours' => 'Int',
        'AddThis' => 'Varchar',
    ];

    private static $many_many = [
        'SocialFeed_Profiles' => 'SocialFeed_Profile',
    ];

    private static $defaults = [
        'SocialFeed_Limit' => 30,
        'CacheHours' => 6,
    ];

    protected $tab;
    protected $useCMSFieldsAlways;

    protected $collection;

	private $hasSetRelationForHidingFromFieldLists = false;

    public function __construct($tab = '', $useCMSFieldsAlways = false, $profileRelation = '')
    {
        parent::__construct();
        $this->tab = $tab;
        $this->useCMSFieldsAlways = $useCMSFieldsAlways;

	    if($profileRelation) {
		    static::$connected_relations[] = $profileRelation;
		    $this->hasSetRelationForHidingFromFieldLists = true;
	    }
    }

	public function setOwner($owner, $ownerBaseClass = null) {
		if(!$this->hasSetRelationForHidingFromFieldLists)
			static::$connected_relations[] = get_class($owner);

		return parent::setOwner($owner, $ownerBaseClass);
	}

    public static function get_extra_config($class, $extension, $args)
    {
        $type = isset($args[2]) ? $args[2] : $class;

        \Config::inst()->update(
            'SocialFeed_Profile',
            'belongs_many_many',
            [
                $type => $class,
            ]
        );

        return [
            'many_many_extraFields' => [
                'SocialFeed_Profiles' => [
                    'Module_Disabled' => 'Boolean',
                ],
            ],
        ];
    }

	public static function get_connected_relations() {
		return array_unique(static::$connected_relations);
	}

    function updateCMSFields(\FieldList $fields)
    {
        if (!$this->useCMSFieldsAlways && ($this->owner instanceof \SiteTree)) {
            return;
        }

        $this->updateFields($fields);
    }

    function updateSettingsFields($fields)
    {
        if (!$this->useCMSFieldsAlways && ($this->owner instanceof \SiteTree)) {
            $this->updateFields($fields);
        }
    }

    protected function updateFields($fields) {
        $fields->addFieldToTab('Root', \Tab::create(
            $this->tab ?: 'SocialPlatforms',
            $this->tab ?: _t('SocialFeed.SOCIAL_PLATFORMS', 'Social Platforms'),
            $gf = \GridField::create(
                'SocialFeed_Profiles',
                _t('SocialFeed.PROFILES', 'Profiles'),
                $this->owner->SocialFeed_Profiles(),
                $config = \GridFieldConfig_RecordEditor::create()
            ),
		    \NumericField::create('SocialFeed_Limit', _t('SocialFeed.LIMIT', 'Limit'))
			        ->setDescription(_t('SocialFeed.DESC-LIMIT', 'Set how many to retrieve at a time')),
            \NumericField::create('CacheHours', _t('SocialFeed.CACHE', 'Cache for'))
                ->setDescription(_t('SocialFeed.DESC-CACHE', 'Set how many hours the results from the various platforms are stored in cache for'))
                ->setAttribute('placeholder', 6),
            \TextField::create('AddThis', _t('SocialFeed.ADDTHIS', 'Add This Profile'))
                ->setDescription(_t('SocialFeed.DESC-ADDTHIS', 'AddThis Profile ID used for sharing (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)'))
                ->setAttribute('placeholder', singleton('SocialFeed_Profile')->setting('AddThis'))
        )
        );

        $config
            ->removeComponentsByType('GridFieldAddNewButton')
            ->addComponent(new \GridFieldAddExistingSearchButton('buttons-before-right'))
            ->addComponent(new \GridFieldAddNewMultiClass);

        if($columns = $config->getComponentByType('GridFieldDataColumns')) {
            $displayColumns = $columns->getDisplayFields($gf);

            if(isset($displayColumns['Enabled'])) {
                $displayColumns['Enabled'] = _t('SocialFeed.ENABLED_GLOBALLY', 'Show (Globally)');
                $columns->setDisplayFields($displayColumns);

                $columns->setFieldFormatting([
                    'Enabled' => function($value, $record) {
                        return $value ? '<span class="ui-button-icon-primary ui-icon btn-icon-accept boolean-yes"></span>' : '<span class="ui-button-icon-primary ui-icon btn-icon-decline boolean-no"></span>';
                    }
                ]);
            }
        }

	    if($detailForm = $config->getComponentByType('GridFieldDetailForm')) {
		    $self = $this->owner;
		    $oldCallback = $detailForm->getItemEditFormCallback();

		    $detailForm->setItemEditFormCallback(function ($form, $controller) use ($self, $detailForm, $oldCallback) {
			    if($oldCallback)
				    $oldCallback($form, $controller);

			    if (isset($controller->record))
				    $record = $controller->record;
			    elseif ($form->Record)
				    $record = $form->Record;
			    else
				    $record = null;

			    if ($record) {
				    $record->Parent = $self;

				    foreach(array_intersect($record->many_many(), \ClassInfo::ancestry($self)) as $relation => $type) {
					    $form->Fields()->removeByName($relation);
				    }

				    $record->setEditFormWithParent($self, $form, $controller);
			    }
		    });
	    }
    }

    public function getFeed() {
        return $this->collection()->all();
    }

    protected function collection() {
        if(!$this->collection) {
	        $cache = $this->owner->CacheHours ?: 6;
            $profiles = $this->owner->SocialFeed_Profiles()->exists() ? $this->owner->SocialFeed_Profiles()->filter('Enabled', 1)->exclude('Module_Disabled', 1) : $this->owner->SocialFeed_Profiles();
            $this->collection = \Object::create('Milkyway\SS\SocialFeed\Collector', $profiles, $this->owner, $this->owner->SocialFeed_Limit, $cache);
        }

        return $this->collection;
    }
}