<?php
/**
 * Milkyway Multimedia
 * _config.php
 *
 * @package social-feed
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 */

ShortcodeParser::get('default')->register('facebook_like', array('SocialPlugins_Service', 'facebook_like_button'));
ShortcodeParser::get('default')->register('twitter_follow', array('SocialPlugins_Service', 'twitter_follow_button'));