<?php
/**
 * Milkyway Multimedia
 * GooglePlus_Follow.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class GooglePlus_PlusOne extends GooglePlus {
	protected $template = 'Google_PlusOneButton';

	public function code()
	{
		return 'google_plus_one';
	}

	public function title()
	{
		return [
			'google_plus_one' => _t('Shortcodable.GOOGLE_PLUS_ONE', 'Google Plus One Button'),
		];
	}
}