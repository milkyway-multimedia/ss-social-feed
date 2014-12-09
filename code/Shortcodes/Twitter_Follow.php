<?php
/**
 * Milkyway Multimedia
 * GooglePlus_Follow.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Twitter_Follow extends Twitter {
	public function code()
	{
		return 'twitter_follow';
	}

	public function title()
	{
		return [
			'twitter_follow' => _t('Shortcodable.TWITTER_FOLLOW', 'Twitter Follow Button'),
		];
	}
} 