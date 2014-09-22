<?php namespace Milkyway\SS\SocialFeed;

use Milkyway\SS\Assets;

/**
 * Milkyway Multimedia
 * Utilities.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Utilities implements \TemplateGlobalProvider {
    private static $_facebook_included;

    public static function require_facebook_script($facebook = null) {
        if(!self::$_facebook_included) {
            if(!$facebook)
                $facebook = singleton('SocialFeed_Facebook');

            $appId = $facebook->getValueFromEnvironment('AppID');

            if(!$appId && $facebook = \SocialFeed_Facebook::get()->first())
                $appId = $facebook->getValueFromEnvironment('AppID');

            if($appId)
                Assets::defer(sprintf(\Director::protocol() . 'connect.facebook.net/%s/all.js#xfbml=1&appId=%s', \i18n::get_locale(), $appId));

            self::$_facebook_included = true;

            return \DBField::create_field('HTMLText', '<div id="fb-root"></div>');
        }

        return '';
    }

    public static function require_twitter_script() {
        Assets::defer(\Director::protocol() . 'platform.twitter.com/widgets.js');
    }

    public static function require_google_plus_script() {
        \Requirements::customScript("window.___gcfg = {lang: '" . str_replace('_', '-', \i18n::get_locale()) . "'};", 'GooglePlus-Locale');
        Assets::defer(\Director::protocol() . 'apis.google.com/js/plusone.js', true);
    }

    private static $_addThis_included;

    public static function addThisJS($profileID = '', $config = []) {
        if(!self::$_addThis_included) {
            if(!$profileID) {
                if($profile = \SocialFeed_Profile::get()->first())
                    $profileID = $profile->getValueFromEnvironment('AddThis');

                if(!$profileID)
                    $profileID = singleton('SocialFeed_Profile')->getValueFromEnvironment('AddThis');

                if(!$profileID)
                    return;
            }

            if(!count($config)) {
                $config = array(
                    'data_track_addressbar' => false,
                    'ui_cobrand' => \SiteConfig::current_site_config()->Title,
                    'ui_header_color' => '#FFFFFF',
                    'ui_header_background' => '#999999',
                );
            }

            \Requirements::insertHeadTags('
        <script>
            var addthis_config = addthis_config || ' . json_encode($config) . ';
        </script>
            ', 'AddThis-Configuration');

            Assets::defer('http://s7.addthis.com/js/300/addthis_widget.js#pubid=' . $profileID, true);
        }
    }

	public static function facebookLink()
	{
		if($id = singleton('SocialFeed_Facebook')->getValueFromEnvironment('Username'))
			return \Controller::join_links(singleton('SocialFeed_Facebook')->url, $id);

		return '';
	}

	public static function twitterLink()
	{
		if($id = singleton('SocialFeed_Twitter')->getValueFromEnvironment('Username'))
			return \Controller::join_links(singleton('SocialFeed_Twitter')->url, $id);

		return '';
	}

	public static function googlePlusLink()
	{
		if($id = singleton('SocialFeed_GooglePlus')->getValueFromEnvironment('Username'))
			return \Controller::join_links(singleton('SocialFeed_GooglePlus')->url, $id);

		return '';
	}

	public static function instagramLink()
	{
		if($id = \SiteConfig::current_site_config()->Instagram_Username)
			return \Controller::join_links('http://instagram.com', $id);

		return '';
	}

    public static function clean_cache() {
        $classes = \ClassInfo::implementorsOf('Milkyway\SS\SocialFeed\Contracts\IsCached');

        foreach($classes as $class) {
            singleton($class)->cleanCache();
        }
    }

    public static function get_template_global_variables() {
        return array(
            'require_facebook_script',
            'require_twitter_script',
            'require_google_plus_script',
            'addThisJS',

	        'facebookLink',
	        'twitterLink',
	        'googlePlusLink',
	        'instagramLink',
        );
    }
} 