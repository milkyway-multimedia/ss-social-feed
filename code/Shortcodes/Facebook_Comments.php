<?php
/**
 * Milkyway Multimedia
 * Facebook_Like.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Shortcodes;

class Facebook_Comments extends Facebook_Like {
	protected $template = 'Facebook_Comments';

	public function code()
	{
		return 'facebook_comments';
	}

	public function title()
	{
		return [
			'facebook_comments'  => _t('Shortcodable.FACEBOOK_COMMENTS_BOX', 'Facebook Comments Box'),
		];
	}

	public function formField() {
		$field = $this->removeFields(parent::formField(), ['action', 'scheme', 'show_faces', 'share', 'for_kids']);

		$field->push(\DropdownField::create(
			'mobile',
			_t('Shortcodable.FB-MOBILE', 'Display mobile compatible version'),
			[
				''  => '(Automatic)',
				'true' => 'Yes',
				'false' => 'No',
			]
		));

		$field->push(\NumericField::create(
			'num_posts',
			_t('Shortcodable.FB-NUM_POSTS', 'Number of posts'),
			30
		));

		$field->push(\DropdownField::create(
			'order_by',
			_t('Shortcodable.FB-ORDER_BY', 'Order by'),
			[
				''  => 'Social (active)',
				'reverse_time' => 'Created DESC',
				'time' => 'Created ASC',
			]
		));

		return $field;
	}

	protected function vars($link, &$arguments, $caption = null, $parser = null) {
		$vars = $this->unsetVars(
			parent::vars($link, $arguments, $caption, $parser),
			['fbAction', 'fbLayout', 'fbFaces', 'fbShare', 'fbForKids']
		);

		if(isset($arguments['mobile']))
			$vars['fbMobile'] = $arguments['mobile'] ? $arguments['mobile'] : false;

		if(isset($arguments['num_posts']))
			$vars['fbNumPosts'] = $arguments['num_posts'] ? $arguments['num_posts'] : false;

		if(isset($arguments['order_by']))
			$vars['fbOrderBy'] = $arguments['order_by'] ? $arguments['order_by'] : false;

		return $vars;
	}
}