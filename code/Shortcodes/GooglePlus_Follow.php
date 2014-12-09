<?php
/**
 * Milkyway Multimedia
 * GooglePlus_Follow.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class GooglePlus_Follow extends GooglePlus {
	public function code()
	{
		return 'google_plus_follow';
	}

	public function title()
	{
		return [
			'google_plus_follow' => _t('Shortcodable.GOOGLE_PLUS_FOLLOW', 'Follow on Google+ Button'),
		];
	}
} 