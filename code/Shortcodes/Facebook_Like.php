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

		return \ArrayData::create(array_merge(
				array(
					'fbLink' => $link,
					'fbScheme' => isset($arguments['scheme']) ? $arguments['scheme'] : false,
					'fbAction' => isset($arguments['action']) ? $arguments['action'] : false,
					'fbFaces' => isset($arguments['faces']) ? $arguments['faces'] : false,
					'fbSend' => isset($arguments['send']) ? $arguments['send'] : false,
				), $arguments)
		)->renderWith('Facebook_LikeButton');
	}

	public function formField() {
		return \CompositeField::create(
			\FieldList::create(
				\TextField::create(
					'link',
					_t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
				)->setAttribute('placeholder', singleton('SocialFeed_Facebook')->getValueFromEnvironment('Username')),
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
					->setMaxLength(50)
			)
		);
	}
} 