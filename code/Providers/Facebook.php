<?php namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\Oauth;

/**
 * Milkyway Multimedia
 * Facebook.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Facebook extends Oauth implements \Flushable
{
	const VERSION = 'v2.2';

	protected $endpoint = 'https://graph.facebook.com';
	protected $url = 'http://facebook.com';

	protected $oauth2provider = 'League\OAuth2\Client\Provider\Facebook';

    protected $defaultType = 'feed';

	protected $allowLargeUnsafeImages = true;

	protected $replaceInUrls = [
		//'/v/'       => '/',
//		'p600x600/' => '',
//		'p480x480/' => '',
//		'p130x130/' => '',
//		's130x130/' => '',
//		'p50x50/'   => '',
//		's50x50/'   => '',
	];

	public function all($settings = [])
	{
		$all = [];

        $type = isset($settings['type']) ? $settings['type'] : $this->defaultType;

		try {
			if($this->accessToken && $token = $this->getUsernameAccessToken($settings['username'])) {
				$settings['query']['access_token'] = $token;
			}

			$body = $this->getBodyFromCache($this->endpoint($settings['username'], $type), $settings);

			if(!isset($body['data']))
				$body['data'] = [];

			foreach ($body['data'] as $key => $post) {
				if ($this->allowed($post)) {
					if(!isset($post['id']))
						$post['id'] = $key+1;
					$all[] = $this->handlePost($post, $settings, $type, $settings['username']);
				}
			}
		} catch (\Exception $e) {
			\Debug::show($e->getMessage());
		}

		return $all;
	}

	protected function one($id, $type = '', $settings = [])
	{
		$post = [];

		try {
			$settings['query']['access_token'] = $this->credentials['consumer_key'] . '|' . $this->credentials['consumer_secret'];
			$settings['id'] = $id;
			$settings['type'] = $type;
			$post = $this->getBodyFromCache($this->endpoint($id, $type), $settings);
		} catch (\Exception $e) {
			\Debug::show($e->getMessage());
		}

		return $post;
	}

	protected function handlePost(array $data, $settings = [], $type = 'feed', $userId = '')
	{
		if(strpos($data['id'], '_') !== false)
			list($userId, $id) = explode('_', $data['id']);
		else
			$id = $data['id'];

		if (isset($settings['username']))
			$userId = $settings['username'];

		$this->getExtraDataVariables($data, $id, $type, $userId);

		$post = [
			'ID'            => $id,
			'Link'          => $this->getLinkFromType($userId, $id, $type),
			'Author'        => isset($data['from']) && isset($data['from']['name']) ? $data['from']['name'] : '',
			'AuthorID'      => isset($data['from']) && isset($data['from']['id']) ? $data['from']['id'] : '',
			'AuthorURL'     => isset($data['from']) && isset($data['from']['id']) ? \Controller::join_links($this->url, $data['from']['id']) : \Controller::join_links($this->url, $userId),
			'Avatar'        => isset($data['from']) && isset($data['from']['id']) ? \Controller::join_links($this->endpoint, $data['from']['id'], 'picture') : '',
			'Title'       => isset($data['title']) ? $data['title']: '',
			'Content'       => isset($data['message']) ? $this->textParser()->text($data['message']) : '',
			'Picture'       => $this->getPictureFromData($data),
			'Cover'         => isset($data['cover']) ? $this->getPictureFromData($data['cover']) : '',
			'Thumbnail'     => isset($data['picture']) ? $data['picture'] : '',
			'ObjectName'    => isset($data['name']) ? $data['name'] : '',
			'ObjectURL'     => isset($data['link']) ? $data['link'] : '',
			'Source'        => isset($data['source']) ? $data['source'] : '',
			'Description'   => isset($data['description']) ? $this->textParser()->text($data['description']) : '',
			'Icon'          => isset($data['icon']) ? $data['icon'] : '',
			'Type'          => isset($data['type']) ? $data['type'] : '',
			'StatusType'    => isset($data['status_type']) ? $data['status_type'] : '',
			'Priority'      => isset($data['created_time']) ? strtotime($data['created_time']) : 0,
			'Posted'        => isset($data['created_time']) ? \DBField::create_field('SS_Datetime', $data['created_time']) : null,
			'LastEdited'    => isset($data['updated_time']) ? \DBField::create_field('SS_Datetime', $data['updated_time']) : null,
			'LikesCount'    => isset($data['likes']) && isset($data['likes']['data']) ? count($data['likes']['data']) : 0,
			'CommentsCount' => isset($data['comments']) && isset($data['comments']['data']) ? count($data['comments']['data']) : 0,

		    // Specifically for events
		    'Location' => isset($data['location']) ? $data['location'] : '',
		    'TicketUrl' => isset($data['ticket_uri']) ? $data['ticket_uri'] : '',
		    'Venue' => isset($data['venue']) && isset($data['venue']['name']) ? $data['venue']['name'] : '',
		    'VenueLink' => isset($data['venue']) && isset($data['venue']['link']) ? $data['venue']['link'] : '',
		    'VenuePageID' => isset($data['venue']) && isset($data['venue']['username']) ? $data['venue']['username'] : '',

		    // Specifically for offers
		    'Expires' => isset($data['expiration_time']) ? \DBField::create_field('SS_Datetime', $data['expiration_time']) : null,
		    'Terms' => isset($data['terms']) ? $this->textParser()->text($data['terms']) : null,
		    'CouponType' => isset($data['coupon_type']) ? $data['coupon_type'] : '',
		    'QRCode' => isset($data['qrcode']) ? $data['qrcode'] : '',
		    'Barcode' => isset($data['barcode']) ? $data['barcode'] : '',
		    'RedeemUrl' => isset($data['redemption_link']) ? $data['redemption_link'] : '',
		    'RedeemCode' => isset($data['redemption_code']) ? $data['redemption_code'] : '',
		];

		if(!$post['ObjectName'] && $post['Title'])
			$post['ObjectName'] = $post['Title'];

		if($post['CouponType'] && !$post['RedeemUrl'])
			$post['RedeemUrl'] = $post['Link'];

		if(!$post['ObjectURL'] && $post['RedeemUrl'])
			$post['ObjectURL'] = $post['RedeemUrl'];

		if(isset($data['rating']))
			$post['Rating'] = $data['rating'];

		if(isset($data['review_text']))
			$post['Content'] = '<p>' . $data['review_text'] . '</p>';

		if(isset($data['reviewer'])) {
			$post['Author'] = isset($data['reviewer']['name']) ? $data['reviewer']['name'] : '';
			$post['AuthorID'] = isset($data['reviewer']['id']) ? $data['reviewer']['id'] : '';
			$post['AuthorURL'] = \Controller::join_links($post['AuthorURL'], 'reviews');
		}

		$post['Created'] = $post['Posted'];
		$post['StyleClasses'] = $post['StatusType'];

		if (isset($data['likes']))
			$post['LikesDescriptor'] = $post['LikesCount'] == 1 ? _t('SocialFeed.LIKE', 'like') : _t('SocialFeed.LIKES', 'likes');

		if (isset($data['comments']))
			$post['CommentsDescriptor'] = $post['CommentsCount'] == 1 ? _t('SocialFeed.COMMENT', 'comment') : _t('SocialFeed.COMMENTS', 'comments');

		// Specifically for events
		if(array_key_exists('is_date_only', $data) || isset($data['start_time']) || isset($data['end_time'])) {
			$dateType = isset($data['is_date_only']) && $data['is_date_only'] ? 'Date' : 'SS_Datetime';

			$post['StartTime'] = isset($data['start_time']) ? \DBField::create_field($dateType, $data['start_time']) : null;
			$post['EndTime'] = isset($data['end_time']) ? \DBField::create_field($dateType, $data['end_time']) : null;

			if(!$post['Posted'])
				$post['Posted'] = $post['StartTime'] ? $post['StartTime'] : $post['EndTime'];

			if(!$post['Priority'])
				$post['Priority'] = isset($data['start_time']) ? strtotime($data['start_time']) : 0;

			if(!$post['Priority'])
				$post['Priority'] = isset($data['end_time']) ? strtotime($data['end_time']) : 0;
		}

		if (!$post['Content'] && isset($data['story']) && $data['story'])
			$post['Content'] = '<p>' . $this->textParser()->text($data['story']) . '</p>';

		if (isset($data['likes']) && isset($data['likes']['data']) && count($data['likes']['data'])) {
			$post['Likes'] = [];

			foreach ($data['likes']['data'] as $likeData) {
				$post['Likes'][] = [
					'Author'    => isset($likeData['name']) ? $likeData['name'] : '',
					'AuthorID'  => isset($likeData['id']) ? $likeData['id'] : '',
					'AuthorURL' => isset($likeData['id']) ? \Controller::join_links($this->url, $likeData['id']) : '',
				];
			}
		}

		if (isset($data['comments']) && isset($data['comments']['data']) && count($data['comments']['data'])) {
			$post['Comments'] = [];

			foreach ($data['comments']['data'] as $commentData) {
				$comment = array(
					'Author'        => isset($commentData['from']) && isset($commentData['from']['name']) ? $commentData['from']['name'] : '',
					'AuthorID'      => isset($commentData['from']) && isset($commentData['from']['id']) ? $commentData['from']['id'] : '',
					'AuthorURL'     => isset($commentData['from']) && isset($commentData['from']['id']) ? \Controller::join_links($this->url, $commentData['from']['id']) : '',
					'Content'       => isset($commentData['message']) ? $commentData['message'] : '',
					'Posted'        => isset($commentData['created_time']) ? \DBField::create_field('SS_Datetime', $commentData['created_time']) : null,
					'ReplyByPoster' => isset($commentData['from']) && isset($commentData['from']['id']) ? $commentData['from']['id'] == $post['AuthorID'] : false,
					'Likes'         => isset($commentData['user_likes']) ? $commentData['user_likes'] : false,
					'LikesCount'    => isset($commentData['like_count']) ? count($commentData['like_count']) : 0,
				);

				$comment['LikesDescriptor'] = $comment['LikesCount'] == 1 ? _t('SocialFeed.LIKE', 'like') : _t('SocialFeed.LIKES', 'likes');
				$post['Comments'][] = $comment;
			}
		}

		if($post['Type'] == 'video' && $post['Source']) {
			$url = parse_url($post['Source']);
			parse_str($url['query'], $query);

			if(isset($query['autoplay'])) {
				unset($query['autoplay']);
			}

			$url = sprintf('%s://%s%s%s', $url['scheme'], $url['host'], $url['path'], (!empty($query) ? '?' . http_build_query($query) : ''));

			$post['ObjectEmbed'] = '<iframe src="' . $url . '" class="panel-post-media--embed" width="640" height="480" frameborder="0" allowfullscreen></iframe>';
		}

		return $post;
	}

	protected function allowed(array $data)
	{
		if (isset($data['is_hidden']) && $data['is_hidden'])
			return false;

		if(isset($data['privacy'])) {
			if(is_array($data['privacy'])) {
				if(isset($data['privacy']['value']) && $data['privacy']['value'] && $data['privacy']['value'] != 'EVERYONE')
					return false;
			}
			elseif($data['privacy'] != 'OPEN')
				return false;
		}

		return true;
	}

	protected function endpoint($username, $type = '')
	{
		return \Controller::join_links($this->endpoint, static::VERSION, $username, $type);
	}

	protected function isValid($body)
	{
		return $body && is_array($body) && count($body);
	}

	protected function getPictureFromData($data) {
		$link = '';

		if(is_array($data)) {
			if(isset($data['images']) && count($data['images']) && isset($data['source']))
				$link = $data['source'];
			elseif(isset($data['cover'])) {
				if(is_array($data['cover']) && isset($data['cover']['source']))
					$link = $data['cover']['source'];
				else
					$link = $data['cover'];
			}
			elseif(isset($data['picture']))
				$link = $data['picture'];
			elseif(isset($data['cover_photo'])) {
				$link = $data['cover_photo'];
			}
			elseif(isset($data['image_url'])) {
				$link = $data['image_url'];
			}
		}
		else
			$link = $data;

		if($this->allowLargeUnsafeImages && strpos($link, '/safe_image.php?') !== false) {
			$parts = parse_url($link);

			if(isset($parts['query'])) {
				parse_str($parts['query'], $query);
				if(isset($query['url'])) {
					$link = $query['url'];
				}
			}
		}

		return $link ? str_replace(array_keys($this->replaceInUrls), array_values($this->replaceInUrls), $link) : '';
	}

	protected function getLinkFromType($userId, $id, $type = 'feed') {
		$link = $this->url;

		switch($type) {
			case 'events':
				$type = 'events';
				break;
			case 'ratings':
				return '';
				break;
			default:
				$type = 'posts';
				$link = \Controller::join_links($this->url, $userId);
				break;
		}

		return \Controller::join_links($link, $type, $id);
	}

	protected function getExtraDataVariables(&$data, $id, $type = 'feed', $userId = '') {
		switch($type) {
			case 'events':
				$data = array_merge($data, $this->one($id));

				if(!isset($data['from']) && isset($data['owner']))
					$data['from'] = $data['owner'];
				break;
			case 'albums':
				if(isset($data['cover_photo']))
					$data['cover'] = $this->one($data['cover_photo']);
				break;
			case 'feed':
				if(isset($data['type']) && $data['type'] == 'photo' && isset($data['object_id']))
					$data['cover'] = $this->one($data['object_id']);
		}
	}

	protected function getUsernameAccessToken($username) {
		if($this->accessToken) {
			try {
				$body = $this->getBodyFromCache($this->endpoint($username), ['query' => ['fields' => 'access_token']]);

				if(isset($body['access_token']))
					return $body['access_token'];
			} catch (\Exception $e) {
				\Debug::show($e->getMessage());
			}
		}

		return null;
	}

	public static function flush() {
		singleton(__CLASS__)->cleanCache();
	}
}