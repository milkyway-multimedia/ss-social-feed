<?php
/**
 * Milkyway Multimedia
 * Facebook_Like.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

use Milkyway\SS\Shortcodes\Contract;
use ArrayData;
use CompositeField;
use TextField;
use DropdownField;
use FieldList;

class Youtube_Subscribe implements Contract {
	public function code()
	{
		return 'youtube_subscribe';
	}

	public function title()
	{
		return [
			'youtube_subscribe'  => _t('Shortcodable.YOUTUBE_SUBSCRIBE', 'Youtube Subscribe Button'),
		];
	}

	public function isAvailableForUse($member = null) {
		return true;
	}

	public function render($arguments, $caption = null, $parser = null)
	{
		return ArrayData::create(array_merge(
				[
					'youtubeChannel' => isset($arguments['channel']) ? $arguments['channel'] : $caption,
					'youtubeLayout' => isset($arguments['layout']) ? $arguments['layout'] : null,
					'youtubeTheme' => isset($arguments['theme']) ? $arguments['theme'] : null,
					'youtubeCounter' => isset($arguments['counter']) ? $arguments['counter'] : null,
				], $arguments)
		)->renderWith('Youtube_SubscribeButton');
	}

	public function formField() {
		return CompositeField::create(
			FieldList::create(
				TextField::create(
					'channel',
					_t('Shortcodable.YOUTUBE-CHANNEL', 'Username/Channel Name')
				),
				DropdownField::create(
					'layout',
					_t('Shortcodable.YOUTUBE-LAYOUT', 'Layout'),
					[
						''  => '(default)',
						'full' => 'full',
					]
				),
				DropdownField::create(
					'theme',
					_t('Shortcodable.YOUTUBE-THEME', 'Theme'),
					[
						''  => '(default)',
						'dark' => 'dark',
					]
				),
				DropdownField::create(
					'counter',
					_t('Shortcodable.ADDTHIS-COUNTER', 'Display counter'),
					[
						''  => '(default - show)',
						'hidden' => 'hidden',
					]
				)
			)
		);
	}
}