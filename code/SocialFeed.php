<?php
/**
 * Milkyway Multimedia
 * SocialFeed.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 */

class SocialFeed extends Page {
	private static $description = 'A page that displays a social feed from various social platforms';

	private static $icon = 'social-feed/images/treeicons/social-feed.png';

	private static $extensions = [
		"Milkyway\\SS\\SocialFeed\\Extensions\\HasProfiles",
	];
}