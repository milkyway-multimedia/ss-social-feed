<?php namespace Milkyway\SocialFeed;
/**
 * Milkyway Multimedia
 * Utilities.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Utilities implements TemplateGlobalProvider {
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

    public static function require_facebook_script() {
        if(!self::$_facebook_included) {
            $facebook = SocialFeed_Facebook::get()->first();
            if(!$facebook) $facebook = singleton('SocialFeed_Facebook');

            if($appId = $facebook->getValueFromEnvironment('AppID'))
                MWMRequirements::defer(sprintf(Director::protocol() . 'connect.facebook.net/%s/all.js#xfbml=1&appId=%s', i18n::get_locale(), $appId), true);

            self::$_facebook_included = true;

            return DBField::create_field('HTMLText', '<div id="fb-root"></div>');
        }

        return '';
    }

    public static function get_template_global_variables() {
        return array(
            'require_facebook_script',
            'require_twitter_script',
            'require_google_plus_script',
        );
    }
} 