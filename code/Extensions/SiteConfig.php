<?php namespace Milkyway\SS\SocialFeed\Extensions;

/**
 * Milkyway Multimedia
 * SiteConfig.php
 *
 * @package relatewell.org.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class SiteConfig extends \DataExtension
{
    private static $db = [
        'Facebook_Username'         => 'Varchar',
        'Facebook_AppId'    => 'Varchar',
        'Facebook_AppSecret'    => 'Varchar',
        'Twitter_Username'          => 'Varchar',
        'Twitter_ApiKey'            => 'Varchar',
        'Twitter_ApiSecret'         => 'Varchar',
        'Twitter_AccessToken'       => 'Varchar',
        'Twitter_AccessTokenSecret' => 'Varchar',
        'GooglePlus_Username'       => 'Varchar',
        'GooglePlus_ApiKey'         => 'Varchar',
        'Instagram_Username'        => 'Varchar(200)',
        'AddThis'                   => 'Varchar(32)',
    ];

    public function updateCMSFields(\FieldList $fields) {
        $fields->addFieldsToTab('Root.Social.Main', [
                \TextField::create('Facebook_Username', _t('SiteConfig.FACEBOOK_PAGE', 'Facebook Page'))
                    ->setDescription('Facebook Page (eg. http://facebook.com/<strong>username</strong> or http://facebook.com/pages/<strong>ID</strong>)')
                    ->setAttribute('placeholder', singleton('SocialFeed_Facebook')->setting('Username')),
                \TextField::create('Twitter_Username', _t('SiteConfig.TWITTER_USERNAME', 'Twitter Username'))
                    ->setDescription('Twitter Username (eg. http://twitter.com/<strong>username</strong>)')
                    ->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('Username')),
                \TextField::create('GooglePlus_Username', _t('SiteConfig.GOOGLE_PLUS_PAGE', 'Google Plus Page'))
                    ->setDescription('ID for your Google Plus Page (eg. http://plus.google.com/<strong>ID</strong>)')
                    ->setAttribute('placeholder', singleton('SocialFeed_GooglePlus')->setting('Username')),
                \TextField::create('Instagram_Username', _t('SiteConfig.INSTAGRAM_USERNAME', 'Instagram Username'))
                    ->setDescription('Instagram Username (eg. @<strong>username</strong>)'),
                \TextField::create('AddThis', _t('SiteConfig.ADD_THIS_PROFILE_ID', 'AddThis Profile ID'))
                    ->setDescription('AddThis Profile ID used throughout the website for sharing etc. (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)')
                    ->setAttribute('placeholder', singleton('SocialFeed_Profile')->setting('AddThis'))
            ]
        );

        $fields->addFieldsToTab('Root.Social.Advanced', [
                \ToggleCompositeField::create('FacebookSettings', _t('SiteConfig.FACEBOOK', 'Facebook'), [
                        \TextField::create('Facebook_AppId', _t('SiteConfig.APPLICATION_ID', 'Application ID'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Facebook')->setting('AppID')),
                        \TextField::create('Facebook_AppSecret', _t('SiteConfig.APPLICATION_SECRET', 'Application Secret'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Facebook')->setting('AppSecret')),
                    ]
                ),
                \ToggleCompositeField::create('TwitterSettings', _t('SiteConfig.TWITTER', 'Twitter'), [
                        \TextField::create('Twitter_ApiKey', _t('SiteConfig.API_KEY', 'API Key'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('ApiKey')),
                        \TextField::create('Twitter_ApiSecret', _t('SiteConfig.API_SECRET', 'API Secret'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('ApiSecret')),
                        \TextField::create('Twitter_AccessToken', _t('SiteConfig.ACCESS_TOKEN', 'Access Token'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('AccessToken')),
                        \TextField::create('Twitter_AccessTokenSecret', _t('SiteConfig.ACCESS_TOKEN_SECRET', 'Access Token Secret'))
                            ->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('AccessTokenSecret')),
                    ]
                ),
                \ToggleCompositeField::create('GooglePlusSettings', _t('SiteConfig.GOOGLE_PLUS', 'Google Plus'), [
                        \TextField::create('GooglePlus_ApiKey', _t('SiteConfig.API_KEY', 'API Key'))
                            ->setAttribute('placeholder', singleton('SocialFeed_GooglePlus')->setting('ApiKey')),
                    ]
                ),
            ]
        );
    }

    public function getFacebook_AppID() {
        return $this->owner->getField('Facebook_AppId');
    }
}