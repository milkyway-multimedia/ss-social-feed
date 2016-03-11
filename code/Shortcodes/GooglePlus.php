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

abstract class GooglePlus implements Contract {
	protected $template = 'Google_FollowButton';

	public function isAvailableForUse($member = null) {
		return true;
	}

	public function render($arguments, $caption = null, $parser = null)
	{
		$link = isset($arguments['link']) ? $arguments['link'] : $caption;
		$user = $caption;

		if($link && !filter_var($link, FILTER_VALIDATE_URL)) {
			$link = '';
			$user = $link;
		}

		return \ArrayData::create(array_merge(
				array(
					'gpLink' => $link,
					'gpUser' => $user,
					'gpAnnotation' => isset($arguments['annotation']) ? $arguments['annotation'] : null,
					'gpSize' => isset($arguments['size']) ? $arguments['size'] : null,
				), $arguments)
		)->renderWith($this->template);
	}

	public function formField() {
		return \CompositeField::create(
			\FieldList::create(
				\TextField::create(
					'link',
					_t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
				)->setAttribute('placeholder', singleton('SocialFeed_GooglePlus')->setting('Username')),
				\DropdownField::create(
					'annotation',
					_t('Shortcodable.G+-ANNOTATIONS', 'Display annotation'),
					[
						'bubble'  => 'Bubble next to button with counter',
						'inline' => 'Inline with avatars of users who have followed page',
						'' => 'None',
					]
				),
				\DropdownField::create(
					'size',
					_t('Shortcodable.G+-SIZE', 'Button size'),
					[
						''  => '(default)',
						'standard' => 'Standard',
						'small' => 'Small',
						'medium' => 'Medium',
						'tall' => 'Tall',
					]
				),
				\DropdownField::create(
					'share',
					_t('Shortcodable.G+-RECOMMENDATIONS', 'Display Recommendations'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				)
			)
		);
	}
}