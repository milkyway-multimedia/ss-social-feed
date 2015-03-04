<?php
/**
 * Milkyway Multimedia
 * Facebook_Like.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Facebook_Send extends Facebook_Like {
	protected $template = 'Facebook_SendButton';

	public function code()
	{
		return 'facebook_send';
	}

	public function title()
	{
		return [
			'facebook_send'  => _t('Shortcodable.FACEBOOK_SEND', 'Facebook Send Button'),
		];
	}

	public function formField() {
		return $this->removeFields(parent::formField(), ['action', 'layout', 'show_faces', 'share']);
	}

	protected function vars($link, &$arguments, $caption = null, $parser = null) {
		return $this->unsetVars(
			parent::vars($link, $arguments, $caption, $parser),
			['fbAction', 'fbLayout', 'fbFaces', 'fbShare']
		);
	}
} 