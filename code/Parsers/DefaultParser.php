<?php namespace Milkyway\SS\SocialFeed\Parsers;
use Milkyway\SS\SocialFeed\Contracts\TextParser;

/**
 * Milkyway Multimedia
 * AutolinkParser.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class DefaultParser implements TextParser {
    public function text($text = '') {
        if (!$text) return $text;

        $text = nl2br($text);

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
}