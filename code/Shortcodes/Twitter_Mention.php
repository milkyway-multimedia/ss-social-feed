<?php
/**
 * Milkyway Multimedia
 * GooglePlus_Follow.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Twitter_Mention extends Twitter {
	protected $template = 'Twitter_MentionButton';

	public function code()
	{
		return 'twitter_mention';
	}

	public function title()
	{
		return [
			'twitter_mention' => _t('Shortcodable.TWITTER_MENTION', 'Twitter Mention Button'),
		];
	}
} 