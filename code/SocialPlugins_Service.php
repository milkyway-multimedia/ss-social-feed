<?php
/**
 * Milkyway Multimedia
 * SocialPlugins_Service.php
 *
 * @package social-feed
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 * @credit http://www.codeofaninja.com/2012/06/display-facebook-feed-on-website.html
 */

class SocialPlugins_Service extends Object {
	protected $plugins = array();
	protected $controller;

	public $limit;

	public $cache = 7200; // (seconds) default: 2 hours

	public $sort;

	public function __construct(SocialFeed_Controller $c) {
		$this->controller = $c;

		if($c->Profiles()->exists()) {
			foreach($c->Profiles() as $profile) {
				if(!$profile->isValid()) continue;

				if($profile->Controller)
					$controller = $profile->Controller;
				elseif(($config = $profile->PlatformConfiguration) && isset($config['controller']))
					$controller = $config['controller'];
				else
					$controller = 'SocialPlugins_' . str_replace(' ', '', ucwords(str_replace('-', ' ', $profile->Type)));

				if(ClassInfo::exists($controller))
					$this->plugins[$profile->UserID] = Object::create($controller, $profile);
				else
					throw new SocialPlugins_Exception('No controller defined for this plugin type: ' . $profile->PlatformType);
			}
		}
	}

	public function setPlugin($name, $controller) {
		$this->plugins[$name] = $controller;
		return $this;
	}

	public function getFeed() {
		$feed = ArrayList::create();

		if(count($this->plugins)) {
			foreach($this->plugins as $id => $plugin)
				$feed->merge($plugin->feed($id));

			if($this->sort) {
				if(is_string($this->sort) && strpos($this->sort, ' ') !== false) {
					$sort = explode(' ', $this->sort);
					$feed = $feed->sort($sort[0], $sort[1]);
				}
				else
					$feed = $feed->sort($this->sort);
			}
			else
				$feed = $feed->sort('Priority', 'DESC');
		}

		return $feed;
	}

	public function feed($id) {
		throw new Exception('Please overwrite this method on your subclass');
	}

	public static function auto_link_text($text = '') {
		if (!$text) return '';

		$pattern = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
		$callback = create_function('$matches', '
			   $url       = array_shift($matches);
			   $url_parts = parse_url($url);

			   /*
			   $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
			   $text = preg_replace("/^www./", "", $text);

			   $last = -(strlen(strrchr($text, "/"))) + 1;
			   if ($last < 0) {
				   $text = substr($text, 0, $last) . "&hellip;";
			   }*/

			   return sprintf(\'<a href="%s" target="_blank">%s</a>\', $url, $url);
		   ');

		$linked = preg_replace_callback($pattern, $callback, $text);

		return preg_replace("/\-+<br \/>/", '<hr class="post-divider" />', $linked);
	}

	public function setLimit($count = 5) {
		$this->limit = $count;
		return $this;
	}

	protected function calculateOffset($current = 0) {
		if ($current)
			$page = $current - 1;
		else
			$page = 0;

		return $page * $this->limit;
	}

	public static function require_twitter_script() {
		MWMRequirements::defer(Director::protocol() . 'platform.twitter.com/widgets.js', true);
	}

	public static function require_google_plus_script() {
		Requirements::customScript("window.___gcfg = {lang: '" . str_replace('_', '-', i18n::get_locale()) . "'};", 'GooglePlus-Locale');
		MWMRequirements::defer(Director::protocol() . 'apis.google.com/js/plusone.js', true);
	}

	public static function get_template_global_variables() {
		return array(
			'require_facebook_script',
			'require_twitter_script',
			'require_google_plus_script',
		);
	}

	public static function facebook_like_button($arguments, $content = null, $parser = null) {
		$link = isset($arguments['link']) ? $arguments['link'] : $content;

		return ArrayData::create(array(
			'fbLink' => $link,
			'fbScheme' => isset($arguments['scheme']) ? $arguments['scheme'] : false,
			'fbAction' => isset($arguments['action']) ? $arguments['action'] : false,
			'fbFaces' => isset($arguments['faces']) ? $arguments['faces'] : false,
			'fbSend' => isset($arguments['send']) ? $arguments['send'] : false,
		))->renderWith('Facebook_LikeButton');
	}

	public static function twitter_follow_button($arguments, $content = null, $parser = null) {
		$link = isset($arguments['link']) ? $arguments['link'] : $content;

		if($link && !filter_var($link, FILTER_VALIDATE_URL)) {
			$link = 'http://twitter.com/' . str_replace('@', '', $link);
			$user = $link;
		}
		else
			$user = isset($arguments['user']) ? $arguments['user'] : '';

		$user = str_replace('@', '', $user);

		return ArrayData::create(array(
			'twitterLink' => $link,
			'twitterShowUser' => isset($arguments['show_user']) ? $arguments['show_user'] : true,
			'twitterUser' => $user,
		))->renderWith('Twitter_FollowButton');
	}
}

class SocialPlugins_Facebook extends SocialPlugins_Service {
	private static $location = 'http://facebook.com';

	private static $api_link = 'https://graph.facebook.com';

	public $facebook;
	public $accessToken;

	public $dateFormat = 'U';

	public function __construct($config) {
		$this->controller = $config;

		if(is_array($config)) {
			$this->limit = isset($config['Limit']) ? $config['Limit'] : 5;
			$appId = isset($config['ApplicationID']) ? $config['ApplicationID'] : '';
			$secret = isset($config['ApplicationSecret']) ? $config['ApplicationSecret'] : '';
		}
		else {
			$this->limit = $config->Limit;
			$appId = $config->setting('ApplicationID');
			$secret = $config->setting('ApplicationSecret');
		}

		if(!$appId || !$secret)
			throw new SocialPlugins_Exception('Please define an Application ID & Secret');

		$this->facebook = new Facebook(array(
			'appId' => $appId,
			'secret' => $secret,
			'fileUpload' => $this->stat('allow_file_uploads'),
		));

		$this->accessToken = $this->facebook->getAccessToken();
	}

	public function feed($id) {
		return $this->getPage($id);
	}

	public function getPage($id, $page = 1, $component = 'feed') {
		$items = ArrayList::create();

		$request = MilkywayRestful::create(
			sprintf('%s/%s/%s', $this->config()->api_link, $id, $component),
			$this->cache
		);

		$request->setQueryString(array(
			'access_token' => $this->accessToken,
			'limit' => $this->limit,
			'offset' => $this->calculateOffset($page),
			'date_format' => $this->dateFormat,
		));

		$response = $request->request();
		$raw = $response->getBody();

		if ($response->isError() || !$raw)
			return $items;

		$data = json_decode($raw, true);

		if (isset($data['data']) && count($data['data'])) {
			$posts = $data['data'];
			$formatComponent = 'format' . ucfirst($component);

			if ($this->hasMethod($formatComponent))
				return $this->$formatComponent($posts, $id);
			else {
				foreach ($posts as $post)
					$items->push(ArrayData::create($post));
			}
		}
		else {
			throw new SocialPlugins_Exception(_t('SocialPlugins_Service.FACEBOOK-NO_DATA_RECEIVED', 'There was no data received. Please check your Facebook Page is public and available for use by the api, and has no restricted permissions, otherwise it cannot be accessed off-site.'));
		}

		return $items;
	}

	public function formatFeed($posts, $id) {
		$items = ArrayList::create();

		foreach ($posts as $post) {
			if (isset($post['is_hidden']) && $post['is_hidden'])
				continue;

			$items->push($this->formatPost($post, $id));
		}

		return $items;
	}

	public function formatPost($post, $id) {
		$postID = explode('_', $post['id']);

		$postLink = $this->config()->location . '/' . $id . '/posts/' . $postID[1];

		$posted = array(
			'_is' => is_array($this->controller) ? 'facebook' : $this->controller->Type,
			'Platform' => is_array($this->controller) ? 'Facebook' : $this->controller->PlatformType,
			'canLikePage' => is_array($this->controller) && isset($this->controller['AllowPageLikes']) ? $this->controller['AllowPageLikes'] : $this->controller->AllowPageLikes,
			'canLikePost' => is_array($this->controller) && isset($this->controller['AllowPostLikes']) ? $this->controller['AllowPostLikes'] : $this->controller->AllowPostLikes,
			'ID' => isset($post['id']) ? $post['id'] : 0,
			'Link' => $postLink,
			'Author' => isset($post['from']) && isset($post['from']['name']) ? $post['from']['name'] : '',
			'AuthorID' => isset($post['from']) && isset($post['from']['id']) ? $post['from']['id'] : '',
			'AuthorURL' => isset($post['from']) && isset($post['from']['id']) ? sprintf('%s/%s', $this->config()->location, $post['from']['id']) : '',
			'Avatar' => isset($post['from']) && isset($post['from']['id']) ? sprintf('%s/%s/picture', $this->config()->api_link, $post['from']['id']) : '',
			'Content' => isset($post['message']) ? $this->auto_link_text(nl2br($post['message'])) : '',
			'Picture' => isset($post['picture']) ? $post['picture'] : '',
			'ObjectName' => isset($post['name']) ? $post['name'] : '',
			'ObjectURL' => isset($post['link']) ? $post['link'] : '',
			'Description' => isset($post['description']) ? $this->auto_link_text(nl2br($post['description'])) : '',
			'Icon' => isset($post['icon']) ? $post['icon'] : '',
			'Type' => isset($post['type']) ? $post['type'] : '',
			'StatusType' => isset($post['status_type']) ? $post['status_type'] : '',
			'Priority' => $post['created_time'],
			'Posted' => isset($post['created_time']) ? DBField::create_field('SS_Datetime', $post['created_time']) : null,
			'LikesCount' => isset($post['likes']) && isset($post['likes']['data']) ? count($post['likes']['data']) : 0,
			'CommentsCount' => isset($post['comments']) && isset($post['comments']['data']) ? count($post['comments']['data']) : 0,
		);

		$posted['LikesDescriptor'] = $posted['LikesCount'] == 1 ? _t('FacebookBlog.LIKE', 'like') : _t('FacebookBlog.LIKES', 'likes');
		$posted['CommentsDescriptor'] = $posted['CommentsCount'] == 1 ? _t('FacebookBlog.COMMENT', 'comment') : _t('FacebookBlog.COMMENTS', 'comments');

		if (!$posted['Content'] && isset($post['story']) && $post['story'])
			$posted['Content'] = $this->auto_link_text(nl2br($post['story']));

		if (isset($post['likes']) && isset($post['likes']['data']) && count($post['likes']['data'])) {
			$posted['Likes'] = ArrayList::create();

			foreach ($post['likes']['data'] as $like) {
				$posted['Likes']->push(ArrayData::create(array(
					'Author' => isset($like['name']) ? $like['name'] : '',
					'AuthorID' => isset($like['id']) ? $like['id'] : '',
					'AuthorURL' => isset($like['id']) ? sprintf('%s/%s', $this->config()->location, $like['id']) : '',
				)));
			}
		}

		if (isset($post['comments']) && isset($post['comments']['data']) && count($post['comments']['data'])) {
			$posted['Comments'] = ArrayList::create();

			foreach ($post['comments']['data'] as $comment) {
				$commented = array(
					'Author' => isset($comment['from']) && isset($comment['from']['name']) ? $comment['from']['name'] : '',
					'AuthorID' => isset($comment['from']) && isset($comment['from']['id']) ? $comment['from']['id'] : '',
					'AuthorURL' => isset($comment['from']) && isset($comment['from']['id']) ? sprintf('%s/%s', $this->config()->location, $post['from']['id']) : '',
					'Content' => isset($comment['message']) ? $comment['message'] : '',
					'Posted' => isset($comment['created_time']) ? DBField::create_field('SS_Datetime', $comment['created_time']) : null,
					'ReplyByPoster' => isset($comment['from']) && isset($comment['from']['id']) ? $comment['from']['id'] == $posted['AuthorID'] : false,
					'Likes' => isset($comment['user_likes']) ? $comment['user_likes'] : false,
					'LikesCount' => isset($comment['like_count']) ? count($comment['like_count']) : 0,
				);

				$commented['LikesDescriptor'] = $commented['LikesCount'] == 1 ? _t('FacebookBlog.LIKE', 'like') : _t('FacebookBlog.LIKES', 'likes');

				$posted['Comments']->push(ArrayData::create($commented));
			}
		}

		return ArrayData::create($posted);
	}
}

use TwitterOAuth\TwitterOAuth as Twitter;

class SocialPlugins_Twitter extends SocialPlugins_Service {
	private static $location = 'http://twitter.com';

	public $twitter;

	public $allowReplies = false;

	public function __construct($config) {
		$this->controller = $config;

		if(is_array($config)) {
			$this->limit = isset($config['Limit']) ? $config['Limit'] : 5;

			$key = isset($config['ConsumerKey']) ? $config['ConsumerKey'] : '';
			$secret = isset($config['ConsumerSecret']) ? $config['ApplicationSecret'] : '';
			$token = isset($config['AccessToken']) ? $config['AccessToken'] : '';
			$tSecret = isset($config['AccessTokenSecret']) ? $config['AccessTokenSecret'] : '';
		}
		else {
			$this->limit = $config->Limit;

			$key = $config->setting('ConsumerKey');
			$secret = $config->setting('ConsumerSecret');
			$token = $config->setting('AccessToken');
			$tSecret = $config->setting('AccessTokenSecret');
		}

		if(!$key || !$secret || !$token || !$tSecret)
			throw new SocialPlugins_Exception('Please define a consumer key, consumer secret, access token and access token secret');

		$this->twitter = new Twitter(array(
			'consumer_key' => $key,
			'consumer_secret' => $secret,
			'oauth_token' => $token,
			'oauth_token_secret' => $tSecret,
			'output_format' => 'json',
		));
	}

	public function allowReplies($flag = true) {
		$this->allowReplies = $flag;
		return $this;
	}

	public function feed($id) {
		return $this->getTimeline($id);
	}

	public function getTimeline($user) {
		$params = array(
			'count' => $this->limit,
			'exclude_replies' => !$this->allowReplies
		);

		if($user)
			$params['screen_name'] = $user;

		$response = $this->getCached($params);

		if(!$response) {
			$response = $this->twitter->get('statuses/user_timeline', $params);
			$this->cache($response, $params);
		}

		$data = json_decode($response, true);

		if(is_array($data))
			return $this->formatTimeline($data);

		throw new SocialPlugins_Exception(_t('SocialPlugins_Service.TWITTER-NO_DATA_RECEIVED', 'There was no data received. Please check your Twitter credentials.'));
	}

	public function formatTimeline($posts) {
		$items = ArrayList::create();

		foreach ($posts as $post)
			$items->push($this->formatPost($post));

		return $items;
	}

	public function formatPost($post) {
		$posted = array(
			'_is' => is_array($this->controller) ? 'twitter' : $this->controller->Type,
			'Platform' => is_array($this->controller) ? 'Twitter' : $this->controller->PlatformType,
			'canFollowAuthor' => is_array($this->controller) && isset($this->controller['AllowAuthorFollows']) ? $this->controller['AllowAuthorFollows'] : $this->controller->AllowAuthorFollows,
			'canMentionAuthor' => is_array($this->controller) && isset($this->controller['AllowAuthorMentions']) ? $this->controller['AllowAuthorMentions'] : $this->controller->AllowAuthorMentions,
			'canRetweetHashTag' => is_array($this->controller) && isset($this->controller['AllowHashTagTweets']) ? $this->controller['AllowHashTagTweets'] : $this->controller->AllowHashTagTweets,
			'ID' => isset($post['id']) ? $post['id'] : 0,
			'Author' => isset($post['user']) && isset($post['user']['screen_name']) ? '@' . $post['user']['screen_name'] : '',
			'AuthorName' => isset($post['user']) && isset($post['user']['screen_name']) ? $post['user']['screen_name'] : '',
			'AuthorID' => isset($post['user']) && isset($post['user']['id']) ? $post['user']['id'] : 0,
			'AuthorURL' => isset($post['user']) && isset($post['user']['url']) ? $post['user']['url'] : '',
			'Avatar' => isset($post['user']) && isset($post['user']['profile_image_url']) ? $post['user']['profile_image_url'] : '',
			'AuthorFollowers' => isset($post['user']) && isset($post['user']['followers_count']) ? $post['user']['followers_count'] : 0,
			'AuthorFriends' => isset($post['user']) && isset($post['user']['friends_count']) ? $post['user']['friends_count'] : 0,
			'Content' => isset($post['text']) ? $this->auto_link_text(nl2br($post['text'])) : '',
			'Favourite' => isset($post['favorited']) ? $post['favorited'] : false,
			'Truncated' => isset($post['truncated']) ? $post['truncated'] : false,
			'Priority' => isset($post['created_at']) ? strtotime($post['created_at']) : 0,
			'Posted' => isset($post['created_at']) ? DBField::create_field('SS_Datetime', strtotime($post['created_at'])) : null,
			'Retweeted' => isset($post['retweeted']) ? $post['retweeted'] : false,
			'Retweets' => isset($post['retweet_count']) ? $post['retweet_count'] : 0,
			'Source' => isset($post['source']) ? $post['source'] : '',
			'ReplyTo' => isset($post['in_reply_to_screen_name']) ? $post['in_reply_to_screen_name'] : '',
			'Sensitive' => isset($post['possibly_sensitive']) ? $post['possibly_sensitive'] : false,
		);

		$posted['RetweetsDescriptor'] = $posted['Retweets'] == 1 ? _t('SocialPlugins_Twitter.RETWEET', 'Retweet') : _t('SocialPlugins_Twitter.RETWEETS', 'Retweets');
		$posted['AuthorFollowersDescriptor'] = $posted['AuthorFollowers'] == 1 ? _t('SocialPlugins_Twitter.FOLLOWER', 'Follower') : _t('SocialPlugins_Twitter.FOLLOWERS', 'Followers');
		$posted['AuthorFriendsDescriptor'] = $posted['AuthorFriends'] == 1 ? _t('SocialPlugins_Twitter.FRIEND', 'Friend') : _t('SocialPlugins_Twitter.FRIENDS', 'Friends');

		if (isset($post['entities'])) {
			if(isset($post['entities']['urls']) && count($post['entities']['urls'])) {
				$posted['URLs'] = ArrayList::create();

				foreach ($post['entities']['urls'] as $url) {
					$posted['URLs']->push(ArrayData::create(array(
						'URL' => $url['url'],
						'OriginalURL' => $url['expanded_url'],
						'DisplayURL' => $url['display_url'],
					)));
				}
			}

			if(isset($post['entities']['hashtags']) && count($post['entities']['hashtags'])) {
				$posted['HashTags'] = ArrayList::create();

				foreach ($post['entities']['hashtags'] as $content) {
					$content = isset($content['text']) ? $content['text'] : '';

					if(!$content) continue;

					$posted['HashTags']->push(ArrayData::create(array(
						'Content' => $content
					)));

					if($posted['Content']) {
						if($posted['canRetweetHashTag']) {
							SocialPlugins_Service::require_twitter_script();
							$posted['Content'] = str_replace('#' . $content, sprintf('<span class="twitter-btn"><a href="https://twitter.com/intent/tweet?button_hashtag=%s" class="twitter-hashtag-button" target="_blank">#%s</a></span>', $content, $content), $posted['Content']);
						}
						else
							$posted['Content'] = str_replace('#' . $content, sprintf('<a href="%s" target="_blank">#%s</a>', $this->config()->location . '/search?q=' . urlencode('#' . $content) . '&src=hash', $content), $posted['Content']);
					}
				}

				$posted['HashTagsDescriptor'] = count($post['entities']['hashtags']) == 1 ? _t('SocialPlugins_Twitter.HASH_TAG', 'Hash Tag') : _t('SocialPlugins_Twitter.HASH_TAGS', 'Hash Tags');
			}

			if(isset($post['entities']['user_mentions']) && count($post['entities']['user_mentions'])) {
				$posted['UserMentions'] = ArrayList::create();

				foreach ($post['entities']['user_mentions'] as $mention) {
					$posted['UserMentions']->push(ArrayData::create(array(
						'ID' => isset($mention['id']) ? $mention['id'] : '',
						'Username' => isset($mention['screen_name']) ? $mention['screen_name'] : '',
						'Name' => isset($mention['name']) ? $mention['name'] : '',
					)));
				}

				$posted['UserMentionsDescriptor'] = count($post['entities']['user_mentions']) == 1 ? _t('SocialPlugins_Twitter.MENTION', 'Mention') : _t('SocialPlugins_Twitter.MENTIONS', 'Mentions');
			}
			else
				$posted['UserMentionsDescriptor'] = _t('SocialPlugins_Twitter.MENTIONS', 'Mentions');
		}

		return ArrayData::create($posted);
	}

	protected function getCachedPath($object) {
		$key = md5(var_export($object, true));
		return TEMP_FOLDER . "/twitter_response_$key";
	}

	protected function getCached($object) {
		if($this->cache <= 0 || isset($_GET['flush']))
			return false;

		$path = $this->getCachedPath($object);

		if(@file_exists($path) && (@filemtime($path) + $this->cache > time()))
			return file_get_contents($path);

		return false;
	}

	protected function cache($response, $object) {
		$path = $this->getCachedPath($object);
		file_put_contents($path, $response);
	}
}

class SocialPlugins_GooglePlus extends SocialPlugins_Service {
	private static $api_link = 'https://www.googleapis.com/plus/v1/people';

	public $google;
	public $apiKey;

	public function __construct($config) {
		$this->controller = $config;

		if(is_array($config)) {
			$this->limit = isset($config['Limit']) ? $config['Limit'] : 5;
			$apiKey = isset($config['APIKey']) ? $config['APIKey'] : '';
		}
		else {
			$this->limit = $config->Limit;
			$apiKey = $config->setting('APIKey');
		}

		if(!$apiKey)
			throw new SocialPlugins_Exception('Please define an API Key');

		$this->apiKey = $apiKey;
	}

	public function feed($id) {
		return $this->getActivities($id);
	}

	public function getActivities($id, $type = 'activities') {
		$items = ArrayList::create();

		$request = MilkywayRestful::create(
			sprintf('%s/%s/%s/public', $this->config()->api_link, $id, $type),
			$this->cache
		);

		$request->setQueryString(array(
			'key' => $this->apiKey,
			'maxResults' => $this->limit,
		));

		$response = $request->request();
		$raw = $response->getBody();

		if ($response->isError() || !$raw)
			return $items;

		$data = json_decode($raw, true);

		if(is_array($data) && isset($data['items'])) {
			if(count($data['items']))
				return $this->formatActivities($data['items'], $id);

			return $items;
		}

		throw new SocialPlugins_Exception(_t('SocialPlugins_Service.GOOGLE-NO_DATA_RECEIVED', 'There was no data received. Please check your Google Plus credentials.'));
	}

	public function formatActivities($posts, $id) {
		$items = ArrayList::create();

		foreach ($posts as $post)
			$items->push($this->formatPost($post, $id));

		return $items;
	}

	public function formatPost($post) {
		$posted = array(
			'_is' => is_array($this->controller) ? 'google-plus' : $this->controller->Type,
			'Platform' => is_array($this->controller) ? 'Google Plus' : $this->controller->PlatformType,
			'canFollowAuthor' => is_array($this->controller) && isset($this->controller['AllowGooglePlusFollows']) ? $this->controller['AllowGooglePlusFollows'] : $this->controller->AllowGooglePlusFollows,
			'canLikePost' => is_array($this->controller) && isset($this->controller['AllowPlusOnes']) ? $this->controller['AllowPlusOnes'] : $this->controller->AllowPlusOnes,
			'ID' => isset($post['id']) ? $post['id'] : 0,
			'Link' => isset($post['url']) ? $post['url'] : '',
			'Author' => isset($post['actor']) && isset($post['actor']['displayName']) ? $post['actor']['displayName'] : '',
			'AuthorID' => isset($post['actor']) && isset($post['actor']['id']) ? $post['actor']['id'] : 0,
			'AuthorURL' => isset($post['actor']) && isset($post['actor']['url']) ? $post['actor']['url'] : '',
			'Avatar' => isset($post['actor']) && isset($post['actor']['image']) && isset($post['actor']['image']['url']) ? $post['actor']['image']['url'] : '',
			'Title' => isset($post['title']) ? $this->auto_link_text(nl2br($post['title'])) : '',
			'Type' => isset($post['object']) && isset($post['object']['objectType']) ? $post['object']['objectType'] : '',
			'Content' => isset($post['object']) && isset($post['object']['content']) ? $this->auto_link_text(nl2br($post['object']['content'])) : '',
			'ReplyCount' => isset($post['object']) && isset($post['object']['replies']) && isset($post['object']['replies']['totalItems']) ? $post['object']['replies']['totalItems'] : 0,
			'LikesCount' => isset($post['object']) && isset($post['object']['plusoners']) && isset($post['object']['plusoners']['totalItems']) ? $post['object']['plusoners']['totalItems'] : 0,
			'ReshareCount' => isset($post['object']) && isset($post['object']['resharers']) && isset($post['object']['resharers']['totalItems']) ? $post['object']['resharers']['totalItems'] : 0,
			'Priority' => isset($post['published']) ? strtotime($post['published']) : 0,
			'Posted' => isset($post['published']) ? DBField::create_field('SS_Datetime', strtotime($post['published'])) : null,
		);

		$posted['ReplyDescriptor'] = $posted['ReplyCount'] == 1 ? _t('SocialPlugins_Google.REPLY', 'Reply') : _t('SocialPlugins_Google.REPLIES', 'Replies');
		$posted['LikesDescriptor'] = $posted['LikesCount'] == 1 ? _t('SocialPlugins_Google.LIKE', 'Plus One') : _t('SocialPlugins_Google.LIKES', 'Plus Ones');
		$posted['ReshareDescriptor'] = $posted['ReshareCount'] == 1 ? _t('SocialPlugins_Google.RESHARE', 'Reshare') : _t('SocialPlugins_Google.RESHARES', 'Reshares');

		if(isset($post['object']) && isset($post['object']['attachments']) && count($post['object']['attachments'])) {
			$posted['Attachments'] = ArrayList::create();

			foreach ($post['object']['attachments'] as $attachment) {
				$posted['Attachments']->push(ArrayData::create(array(
					'ID' => isset($attachment['id']) ? $attachment['id'] : '',
					'Type' => isset($attachment['objectType']) ? $attachment['objectType'] : '',
					'Link' => isset($attachment['url']) ? $attachment['url'] : '',
					'Content' => isset($attachment['content']) ? $attachment['content'] : '',
					'Picture' => isset($attachment['image']) && isset($attachment['image']['url']) ? $attachment['image']['url'] : '',
					'Username' => isset($attachment['screen_name']) ? $attachment['screen_name'] : '',
					'Name' => isset($attachment['name']) ? $attachment['name'] : '',
				)));
			}
		}

		return ArrayData::create($posted);
	}
}

class SocialPlugins_Exception extends Exception {

}