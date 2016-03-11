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

class Facebook_Like implements Contract {
	protected $url = 'http://facebook.com/';

	protected $template = 'Facebook_LikeButton';

	public function code()
	{
		return 'facebook_like';
	}

	public function title()
	{
		return [
			'facebook_like'  => _t('Shortcodable.FACEBOOK_LIKE', 'Facebook Like Button'),
		];
	}

	public function isAvailableForUse($member = null) {
		return true;
	}

	public function render($arguments, $caption = null, $parser = null)
	{
		$link = isset($arguments['link']) ? $arguments['link'] : $caption;

		if($link && !filter_var($link, FILTER_VALIDATE_URL)) {
			$link = \Controller::join_links($this->url, str_replace('@', '', $link));
		}

		return \ArrayData::create(array_merge($this->vars($link, $arguments, $caption, $parser), $arguments))->renderWith($this->template);
	}

	public function formField() {
		return \CompositeField::create(
			\FieldList::create(
				\TextField::create(
					'link',
					_t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
				)->setAttribute('placeholder', singleton('SocialFeed_Facebook')->setting('Username')),
				\DropdownField::create(
					'action',
					_t('Shortcodable.FB-ACTION', 'Action'),
					[
						''  => '(default)',
						'like' => 'Like',
						'recommend' => 'Recommend',
					]
				),
				\DropdownField::create(
					'scheme',
					_t('Shortcodable.FB-SCHEME', 'Scheme'),
					[
						''  => '(default)',
						'light' => 'light',
						'dark' => 'dark',
					]
				),
				\DropdownField::create(
					'layout',
					_t('Shortcodable.FB-LAYOUT', 'Layout'),
					[
						''  => '(default)',
						'standard' => 'Standard',
						'button_count' => 'Button Count',
						'box_count' => 'Box Count',
					]
				),
				\DropdownField::create(
					'share',
					_t('Shortcodable.FB-SHARE', 'Allow Sharing'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				),
				\DropdownField::create(
					'show_faces',
					_t('Shortcodable.FB-SHOW_FACES', 'Show profile photos'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				),
				\TextField::create(
					'ref',
					_t('Shortcodable.FB-REFERRAL_REFERENCE', 'Reference for referrals')
				)
					->setDescription('A label for tracking referrals which must be less than 50 characters')
					->setMaxLength(50),
				\DropdownField::create(
					'for_kids',
					_t('Shortcodable.FB-FOR_KIDS', 'Display for kid directed website specifically?'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				)
			)
		);
	}

	protected function vars($link, &$arguments, $caption = null, $parser = null) {
		return [
			'fbLink' => $link,
			'fbScheme' => isset($arguments['scheme']) ? $arguments['scheme'] : false,
			'fbAction' => isset($arguments['action']) ? $arguments['action'] : false,
			'fbLayout' => isset($arguments['layout']) ? $arguments['layout'] : false,
			'fbFaces' => isset($arguments['show_faces']) ? $arguments['show_faces'] : false,
			'fbShare' => isset($arguments['share']) ? $arguments['share'] : false,
			'fbForKids' => isset($arguments['for_kids']) ? $arguments['for_kids'] : false,
			'fbRef' => isset($arguments['ref']) ? $arguments['ref'] : false,
		];
	}

	protected function unsetVars($vars, $varsToUnset = []) {
		foreach($varsToUnset as $toUnset) {
			if(isset($vars[$toUnset]))
				unset($vars[$toUnset]);
		}

		return $vars;
	}

	protected function removeFields($fields, $fieldsToRemove = []) {
		foreach($fieldsToRemove as $field) {
			$fields->removeByName($field);
		}

		return $fields;
	}
}