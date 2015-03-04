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

class AddThis implements Contract {
	public function code()
	{
		return 'addthis';
	}

	public function title()
	{
		return [
			'addthis'        => _t('Shortcodable.ADDTHIS', 'Share This Page Button Set'),
		];
	}

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

		if(isset($arguments['user']))
			$user = $arguments['user'];

		return \ArrayData::create(array_merge(
				array(
					'addThisUrl' => $link,
					'addThisProfileID' => $user,
					'addThisTitle' => isset($arguments['title']) ? $arguments['title'] : null,
					'addThisCounter' => isset($arguments['counter']) ? $arguments['counter'] : null,
				), $arguments)
		)->renderWith('AddThis_ShareModule');
	}

	public function formField() {
		return \CompositeField::create(
			\FieldList::create(
				\TextField::create(
					'link',
					_t('Shortcodable.LINK', 'Link')
				)->setAttribute('placeholder', _t('Shortcodable.DEFAULT-ADDTHIS-LINK', 'Current Page URL')),
				\TextField::create(
					'title',
					_t('Shortcodable.TITLE', 'Title')
				)->setAttribute('placeholder', _t('Shortcodable.DEFAULT-ADDTHIS-TITLE', 'Current Page Title')),
				\DropdownField::create(
					'counter',
					_t('Shortcodable.ADDTHIS-COUNTER', 'Display counter'),
					[
						''  => 'No',
						'1' => 'Yes',
					]
				),
				\TextField::create(
					'user',
					_t('Shortcodable.ADDTHIS-PROFILEID', 'Profile ID')
				)
					->setDescription('AddThis Profile ID used throughout the website for sharing etc. (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)')
					->setAttribute('placeholder', singleton('SocialFeed_Profile')->setting('AddThis'))
			)
		);
	}
} 