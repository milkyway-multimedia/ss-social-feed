<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Profile.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use \Milkyway\SS\SocialFeed\Extensions\HasProfiles as HasProfiles;

class SocialFeed_Profile extends DataObject {

    private static $singular_name = 'Profile';

    private static $db = [
        'Username' => 'Varchar',
        'Limit'    => 'Int',
        'Enabled'  => 'Boolean',
        'AddThis' => 'Varchar',
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
        'PlatformDetails',
        'Enabled',
        'Limit',
    ];

    private static $templates = [
        'SocialFeed_Post',
    ];

	private static $db_to_environment_mapping = [
		'AddThis' => 'SocialFeed|SiteConfig.addthis_profile_id',
	];

    protected $provider = 'Milkyway\SS\SocialFeed\Providers\Models\HTTP';

    public function canCreate($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if(get_class($this) == 'SocialFeed_Profile')
                    return false;

                if($this->Parent && $this->Parent->canCreate($member))
                    return true;
            }
        );

        return parent::canCreate($member);
    }

    public function canEdit($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent && $this->Parent->canEdit($member))
                    return true;
            }
        );

        return parent::canEdit($member);
    }

    public function canDelete($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent && $this->Parent->canDelete($member))
                    return true;
            }
        );

        return parent::canDelete($member);
    }

    public function canView($member = null) {
        $this->beforeExtending(__FUNCTION__, function($member = null) {
                if($this->Parent && $this->Parent->canView($member))
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

		        foreach(HasProfiles::get_connected_relations() as $relation) {
			        $fields->removeByName($relation);
		        }

		        if(!empty($this->RequiresExtendedPermissions)) {
			        \Object::create($this->Provider, (array) $this->ProviderConfiguration)->extendedPermissions(array_merge(
                        $this->RequiresExtendedPermissions,
                        [
                            'no_live_request' => false,
                        ]
                    ));
		        }
            }
        );

        $this->afterExtending('updateCMSFields', function($fields) {
                $dataFields = $fields->dataFields();

                foreach($dataFields as $field) {
                    if($field instanceof \TextField) {
	                    if(!$field->getAttribute('placeholder'))
                            $field->setAttribute('placeholder', $this->setting($field->Name));
                    }
                }
            }
        );

		return parent::getCMSFields();
	}

	public function setEditFormWithParent($parent, $form, $controller) {
		$dataFields = $form->Fields()->dataFields();

		foreach($dataFields as $field) {
			if($field instanceof \TextField) {
				if(!$field->getAttribute('placeholder'))
					$field->setAttribute('placeholder', $this->setting($field->Name, $parent));
			}
		}

		$this->extend('updateEditFormWithParent', $parent, $form, $controller);
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
        return $this->setting('Username') . ' (' . $this->i18n_singular_name() . ')';
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getProviderConfiguration() {
        return [];
    }

    public function getFeedSettings($parent = null) {
        return [
            'username' => $this->setting('Username', $parent),
            'limit' => $this->Limit,
        ];
    }

    public function getPostSettings($parent = null) {
        return [
            'AddThisProfileID' => $this->setting('AddThis', $parent),
        ];
    }

    public function processPost(array $post, $postObject = null) {
        return $post;
    }

    public function getPlatform() {
        return $this->i18n_singular_name();
    }

	public function getPlatformDetails() {
		return \DBField::create_field('HTMLText', $this->provideDetailsForPlatform());
	}

    public function getTemplates() {
        return array_reverse($this->config()->templates);
    }

    public function getStyleClasses() {
        return singleton('mwm')->raw2htmlid('platform-' . str_replace('_', '-', strtolower($this->singular_name())));
    }

    public function Link($parent = null) {
        return Controller::join_links($this->config()->url, $this->setting('Username', $parent));
    }

    public function setting($setting, $parent = null, $cache = true) {
	    $callbacks = [];

	    if(\ClassInfo::exists('SiteConfig')) {
            if(!in_array($setting, array_keys(singleton('SocialFeed_Profile')->config()->db_to_environment_mapping)))
		        $prefix = get_class($this) == 'SocialFeed_Profile' ? '' : str_replace('SocialFeed_', '', get_class($this)).'_';
            else
                $prefix = '';

		    $callbacks['SiteConfig'] = function($keyParts, $key) use($prefix, $setting) {
			    return SiteConfig::current_site_config()->{$prefix.$setting};
		    };
	    }

        return singleton('env')->get($setting, null, [
            'objects' => [$this, $parent],
            'beforeConfigNamespaceCheckCallbacks' => $callbacks,
            'fromCache' => $cache,
            'doCache' => $cache,
        ]);
    }

	protected function provideDetailsForPlatform() {
		$details = $this->detailsForPlatform();

		$rendered = count($details) ? implode(' ', array_map(function($setting, $value) {
			return '<li>' . $setting . ': ' . $value . '</li>';
		}, array_keys($details), $details)) : '';

		if($rendered)
			$rendered = '<ul class="socialFeed--platformDetails">' . $rendered . '</ul>';

		$this->extend('onRenderPlatformDetails', $details, $rendered);

		return $rendered;
	}

	protected function detailsForPlatform() {
		return [
			$this->fieldLabel('Username') => $this->Username,
		];
	}
}