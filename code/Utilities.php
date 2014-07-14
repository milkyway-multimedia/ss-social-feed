<?php namespace Milkyway\SocialFeed;

use Milkyway\Assets;

/**
 * Milkyway Multimedia
 * Utilities.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Utilities implements \TemplateGlobalProvider {
    public static function auto_link_text($text = '') {
        if (!$text) return '';

        $pattern = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
        $callback = create_function('$matches', '
			   $url       = array_shift($matches);
			   $url_parts = parse_url($url);

			   /*
			   $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
			   $text = preg_replace("/^www./", "", $text);

			   $last = -(strlen(strrchr($text, "/"))) + 1;
			   if ($last < 0) {
				   $text = substr($text, 0, $last) . "&hellip;";
			   }*/

			   return sprintf(\'<a href="%s" target="_blank">%s</a>\', $url, $url);
		   ');

        $linked = preg_replace_callback($pattern, $callback, $text);

        return preg_replace("/\-+<br \/>/", '<hr class="post-divider" />', $linked);
    }

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

    private static $_addThis_included;

    public static function addThisJS($profileID = '', $config = []) {
        if(!self::$_addThis_included) {
            if(!$profileID) {
                if($profile = \SocialFeed_Profile::first())
                    $profileID = $profile->getValueFromEnvironment('AppID');

                if(!$profileID)
                    $profileID = singleton('SocialFeed_Profile')->getValueFromEnvironment('AppID');

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

            self::$_facebook_included = true;
        }
    }

    public static function get_template_global_variables() {
        return array(
            'require_facebook_script',
            'require_twitter_script',
            'require_google_plus_script',
            'addThisJS',
        );
    }
} 