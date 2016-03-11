<?php
/**
 * Milkyway Multimedia
 * GooglePlus.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;
use Milkyway\SS\Shortcodes\Contract;

abstract class Twitter implements Contract {
	protected $template = 'Twitter_FollowButton';

	protected $url = 'http://twitter.com/';

	public function isAvailableForUse($member = null) {
		return true;
	}

	public function render($arguments, $caption = null, $parser = null)
	{
		$link = isset($arguments['link']) ? $arguments['link'] : $caption;

		if($link && !filter_var($link, FILTER_VALIDATE_URL)) {
			$user = $link;
			$link = \Controller::join_links($this->url, str_replace('@', '', $link));
		}
		else
			$user = isset($arguments['user']) ? $arguments['user'] : '';

		$user = str_replace('@', '', $user);

		return \ArrayData::create(array_merge(
				array(
					'twitterLink' => $link,
					'twitterShowUser' => isset($arguments['show_user']) ? $arguments['show_user'] : true,
					'twitterUser' => $user,
				), $arguments)
		)->renderWith($this->template);
	}

	public function formField() {
		return \CompositeField::create(
			\FieldList::create(
				\TextField::create(
					'link',
					_t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
				)->setAttribute('placeholder', singleton('SocialFeed_Twitter')->setting('Username')),
				\DropdownField::create(
					'share',
					_t('Shortcodable.TWITTER-COUNT', 'Show count'),
					[
						'1'  => 'Yes',
						'' => 'No',
					]
				),
				\DropdownField::create(
					'show_faces',
					_t('Shortcodable.FB-SHOW_SCREEN_NAME', 'Show screen name'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				)
			)
		);
	}
}