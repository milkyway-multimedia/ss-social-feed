<?php
/**
 * Milkyway Multimedia
 * Facebook_Like.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Facebook_Follow extends Facebook_Like {
	protected $template = 'Facebook_FollowButton';

	public function code()
	{
		return 'facebook_follow';
	}

	public function title()
	{
		return [
			'facebook_follow'  => _t('Shortcodable.FACEBOOK_FOLLOW', 'Facebook Follow Button'),
		];
	}

	public function formField() {
		return $this->removeFields(parent::formField(), ['share']);
	}

	protected function vars($link, &$arguments, $caption = null, $parser = null) {
		return $this->unsetVars(
			parent::vars($link, $arguments, $caption, $parser),
			['fbShare']
		);
	}
}