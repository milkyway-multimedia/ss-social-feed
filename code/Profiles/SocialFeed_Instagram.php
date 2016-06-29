<?php

/**
 * Milkyway Multimedia
 * SocialFeed_Instagram.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class SocialFeed_Instagram extends SocialFeed_Profile
{
    private static $url = 'http://instagram.com';

    private static $singular_name = 'Instagram';

    private static $db = [
        'Type'              => 'Enum(array("recent","liked"))',
        'AppID'             => 'Varchar',
        'AppSecret'         => 'Varchar',
        'Author'            => 'Varchar',
        'AllowHashTagLinks' => 'Boolean',
    ];

    private static $db_to_environment_mapping = [
        'AppID'     => 'Instagram|SocialFeed|SiteConfig.instagram_application_id',
        'AppSecret' => 'Instagram|SocialFeed|SiteConfig.instagram_application_secret',
    ];

    protected $provider = 'Milkyway\SS\SocialFeed\Providers\Instagram';

    public function canCreate($member = null)
    {
        $this->beforeExtending(__FUNCTION__, function () {
            if (!class_exists('League\OAuth2\Client\Provider\Instagram')) {
                return false;
            }
        });

        return parent::canCreate($member);
    }

    public function getCMSFields()
    {
        $this->beforeExtending(
            'updateCMSFields',
            function ($fields) {
                if ($type = $fields->dataFieldByName('Type')) {
                    $types = [
                        'recent' => _t('SocialFeed_Instagram.RECENT', 'Recent uploads'),
                        'liked'  => _t('SocialFeed_Instagram.LIKED', 'Liked'),
                    ];

                    $type->setSource($types);
                }
            }
        );

        return parent::getCMSFields();
    }

    public function getTitle()
    {
        return parent::getTitle() . ' - ' . $this->Type;
    }

    public function getProviderConfiguration()
    {
        return [
            'consumer_key'    => $this->setting('AppID'),
            'consumer_secret' => $this->setting('AppSecret'),
        ];
    }

    public function getRequiresExtendedPermissions()
    {
        return ['scopes' => '_all'];
    }

    public function getFeedSettings($parent = null)
    {
        return array_merge(parent::getFeedSettings($parent), [
                'type'  => $this->Type,
                'query' => [
                    'count' => $this->Limit,
                ],
            ]
        );
    }

    public function processPost(array $post, $postObject = null)
    {
        $post = parent::processPost($post, $postObject);

        if ($this->AllowHashTagLinks) {
            if ($post['Content']) {
                $post['Content'] = $this->addHashTags($post['Content']);
            }

            if ($post['Description']) {
                $post['Description'] = $this->addHashTags($post['Description']);
            }
        }

        return $post;
    }

    protected function detailsForPlatform()
    {
        return array_merge(parent::detailsForPlatform(), [
            $this->fieldLabel('Type') => $this->Type,
        ]);
    }

    protected function addHashTags($content)
    {
        return preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/',
            str_replace('{url}', \Controller::join_links($this->url, 'explore/tags') . '/',
                '\1#<a href="{url}\2" target="\_blank">\2</a>'), $content);
    }
} 