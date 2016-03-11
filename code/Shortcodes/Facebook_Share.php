<?php
/**
 * Milkyway Multimedia
 * Facebook_Like.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Facebook_Share extends Facebook_Like {
	protected $template = 'Facebook_ShareButton';

	public function code()
	{
		return 'facebook_share';
	}

	public function title()
	{
		return [
			'facebook_share'  => _t('Shortcodable.FACEBOOK_SHARE', 'Facebook Share Button'),
		];
	}

	public function formField() {
		return $this->removeFields(parent::formField(), ['action', 'scheme', 'show_faces', 'share', 'for_kids']);
	}

	protected function vars($link, &$arguments, $caption = null, $parser = null) {
		return $this->unsetVars(
			parent::vars($link, $arguments, $caption, $parser),
			['fbAction', 'fbScheme', 'fbFaces', 'fbShare', 'fbForKids']
		);
	}
}