<?php
/**
 * Milkyway Multimedia
 * _config.php
 *
 * @package social-feed
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 */

ShortcodeParser::get('default')->register('google_plus_follow', array('SocialFeed_GooglePlus', 'google_plus_follow_shortcode'));
ShortcodeParser::get('default')->register('google_plus_one', array('SocialFeed_GooglePlus', 'google_plus_one_shortcode'));

ShortcodeParser::get('default')->register('twitter_follow', array('SocialFeed_Twitter', 'twitter_follow_shortcode'));
ShortcodeParser::get('default')->register('twitter_mention', array('SocialFeed_Twitter', 'twitter_mention_shortcode'));

ShortcodeParser::get('default')->register('facebook_like', array('SocialFeed_Facebook', 'facebook_like_shortcode'));

ShortcodeParser::get('default')->register('addthis', array('SocialFeed_Profile', 'addthis_shortcode'));