<?php namespace Milkyway\SS\SocialFeed;

use Milkyway\SS\Director;
use SocialFeed_Facebook;
use SocialFeed_Profile;
use SiteConfig;
use Controller;
use i18n;

/**
 * Milkyway Multimedia
 * Utilities.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Utilities implements \TemplateGlobalProvider
{
    public static function require_facebook_script($facebook = null, $parent = null)
    {
        if (!$facebook) {
            $facebook = singleton('SocialFeed_Facebook');
        }

        $appId = $facebook->setting('AppID', $parent);

        if (!$appId && $facebook = SocialFeed_Facebook::get()->first()) {
            $appId = $facebook->setting('AppID', $parent);
        }

        if ($appId) {
            singleton('require')->defer(sprintf(Director::protocol() . 'connect.facebook.net/%s/all.js#xfbml=1&appId=%s',
                i18n::get_locale(), $appId));
        }

        singleton('env')->set('include_facebook_root_div', true);
    }

    public static function require_twitter_script()
    {
        singleton('require')->defer(Director::protocol() . 'platform.twitter.com/widgets.js');
    }

    public static function require_google_plus_script()
    {
        singleton('require')->customScript("window.___gcfg = {lang: '" . str_replace('_', '-',
                i18n::get_locale()) . "'};", 'GooglePlus-Locale');
        singleton('require')->defer(Director::protocol() . 'apis.google.com/js/plusone.js', true);
    }

    public static function require_google_platform_script()
    {
        singleton('require')->defer(Director::protocol() . 'apis.google.com/js/platform.js', true);
    }

    private static $_addThis_included;

    public static function addThisJS($profileID = '', $parent = null, $config = [])
    {
        if (!self::$_addThis_included) {
            if (!$profileID) {
                if ($profile = SocialFeed_Profile::get()->first()) {
                    $profileID = $profile->setting('AddThis', $parent);
                }

                if (!$profileID) {
                    $profileID = singleton('SocialFeed_Profile')->setting('AddThis', $parent);
                }

                if (!$profileID) {
                    return;
                }
            }

            if (!count($config)) {
                $config = array(
                    'data_track_addressbar' => false,
                    'ui_cobrand' => SiteConfig::current_site_config()->Title,
                    'ui_header_color' => '#FFFFFF',
                    'ui_header_background' => '#999999',
                );
            }

            singleton('require')->insertHeadTags('
        <script>
            var addthis_config = addthis_config || ' . json_encode($config) . ';
        </script>
            ', 'AddThis-Configuration');

            singleton('require')->defer('http://s7.addthis.com/js/300/addthis_widget.js#pubid=' . $profileID, true);
        }
    }

    public static function facebookLink($parent = null)
    {
        if ($id = singleton('SocialFeed_Facebook')->setting('Username', $parent)) {
            return Controller::join_links(singleton('SocialFeed_Facebook')->config()->url, $id);
        }

        return '';
    }

    public static function twitterLink($parent = null)
    {
        if ($id = singleton('SocialFeed_Twitter')->setting('Username', $parent)) {
            return Controller::join_links(singleton('SocialFeed_Twitter')->config()->url, $id);
        }

        return '';
    }

    public static function googlePlusLink($parent = null)
    {
        if ($id = singleton('SocialFeed_GooglePlus')->setting('Username', $parent)) {
            return Controller::join_links(singleton('SocialFeed_GooglePlus')->config()->url, $id);
        }

        return '';
    }

    public static function instagramLink()
    {
        if ($id = SiteConfig::current_site_config()->Instagram_Username) {
            return Controller::join_links('http://instagram.com', $id);
        }

        return '';
    }

    public static function get_template_global_variables()
    {
        return array(
            'require_facebook_script',
            'require_twitter_script',
            'require_google_plus_script',
            'require_google_platform_script',
            'addThisJS',
            'facebookLink',
            'twitterLink',
            'googlePlusLink',
            'instagramLink',
        );
    }
} 