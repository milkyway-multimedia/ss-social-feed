<?php namespace Milkyway\SocialFeed\Providers;

use Milkyway\SocialFeed\Providers\Model\Oauth;
use Milkyway\SocialFeed\Utilities;

/**
 * Milkyway Multimedia
 * Twitter.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Twitter extends Oauth {
    const VERSION = 1.1;

    protected $endpoint = 'https://api.twitter.com/';
    protected $url = 'http://twitter.com';

    protected $defaultType = 'statuses/user_timeline';

    public function all($settings = []) {
        $all = [];

        $type = isset($settings['type']) ? $settings['type'] : $this->defaultType;

        try {
            $body = $this->getBodyFromCache($this->endpoint($type), $settings);

            foreach($body as $post) {
                $all[] = $this->handlePost($post, $settings);
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

    protected function handlePost(array $data, $settings = []) {
        $post = [
            'ID' => isset($data['id']) ? $data['id'] : 0,
            'Author' => isset($data['user']) && isset($data['user']['screen_name']) ? '@' . $data['user']['screen_name'] : '',
            'AuthorName' => isset($data['user']) && isset($data['user']['screen_name']) ? $data['user']['screen_name'] : '',
            'AuthorID' => isset($data['user']) && isset($data['user']['id']) ? $data['user']['id'] : 0,
            'AuthorURL' => isset($data['user']) && isset($data['user']['url']) ? $data['user']['url'] : '',
            'Avatar' => isset($data['user']) && isset($data['user']['profile_image_url']) ? $data['user']['profile_image_url'] : '',
            'AuthorFollowers' => isset($data['user']) && isset($data['user']['followers_count']) ? $data['user']['followers_count'] : 0,
            'AuthorFriends' => isset($data['user']) && isset($data['user']['friends_count']) ? $data['user']['friends_count'] : 0,
            'Content' => isset($data['text']) ? Utilities::auto_link_text(nl2br($data['text'])) : '',
            'Favourite' => isset($data['favorited']) ? $data['favorited'] : false,
            'Truncated' => isset($data['truncated']) ? $data['truncated'] : false,
            'Priority' => isset($data['created_at']) ? strtotime($data['created_at']) : 0,
            'Posted' => isset($data['created_at']) ? \DBField::create_field('SS_Datetime', strtotime($data['created_at'])) : null,
            'Retweeted' => isset($data['retweeted']) ? $data['retweeted'] : false,
            'Retweets' => isset($data['retweet_count']) ? $data['retweet_count'] : 0,
            'Source' => isset($data['source']) ? $data['source'] : '',
            'ReplyTo' => isset($data['in_reply_to_screen_name']) ? $data['in_reply_to_screen_name'] : '',
            'Sensitive' => isset($data['possibly_sensitive']) ? $data['possibly_sensitive'] : false,
        ];

        $post['Created'] = $post['Posted'];

        $post['RetweetsDescriptor'] = $post['Retweets'] == 1 ? _t('SocialFeed.RETWEET', 'Retweet') : _t('SocialFeed.RETWEETS', 'Retweets');
        $post['AuthorFollowersDescriptor'] = $post['AuthorFollowers'] == 1 ? _t('SocialFeed.FOLLOWER', 'Follower') : _t('SocialFeed.FOLLOWERS', 'Followers');
        $post['AuthorFriendsDescriptor'] = $post['AuthorFriends'] == 1 ? _t('SocialFeed.FRIEND', 'Friend') : _t('SocialFeed.FRIENDS', 'Friends');

        if (isset($data['entities'])) {
            if(isset($data['entities']['urls']) && count($data['entities']['urls'])) {
                $post['URLs'] = [];

                foreach ($data['entities']['urls'] as $url) {
                    $post['URLs'][] = [
                        'URL' => $url['url'],
                        'OriginalURL' => $url['expanded_url'],
                        'DisplayURL' => $url['display_url'],
                    ];
                }
            }

            if(isset($data['entities']['hashtags']) && count($data['entities']['hashtags'])) {
                $post['HashTags'] = [];

                foreach ($data['entities']['hashtags'] as $content) {
                    $content = isset($content['text']) ? $content['text'] : '';

                    if(!$content) continue;

                    $post['HashTags'][] = [
                        'Title' => $content,
                        'forTemplate' => $content,
                        'Content' => $content,
                    ];
                }

                $post['HashTagsDescriptor'] = count($data['entities']['hashtags']) == 1 ? _t('SocialFeed.HASH_TAG', 'Hash Tag') : _t('SocialFeed.HASH_TAGS', 'Hash Tags');
            }

            if(isset($data['entities']['user_mentions']) && count($data['entities']['user_mentions'])) {
                $post['UserMentions'] = [];

                foreach ($data['entities']['user_mentions'] as $mention) {
                    $post['UserMentions'][] = [
                        'ID' => isset($mention['id']) ? $mention['id'] : '',
                        'Username' => isset($mention['screen_name']) ? $mention['screen_name'] : '',
                        'Name' => isset($mention['name']) ? $mention['name'] : '',
                    ];
                }

                $post['UserMentionsDescriptor'] = count($data['entities']['user_mentions']) == 1 ? _t('SocialFeed.MENTION', 'Mention') : _t('SocialFeed.MENTIONS', 'Mentions');
            }
            else
                $post['UserMentionsDescriptor'] = _t('SocialFeed.MENTIONS', 'Mentions');
        }

        return $post;
    }

    protected function endpoint($username, $type = 'statuses/user_timeline') {
        return \Controller::join_links($this->endpoint, static::VERSION, $type . '.json');
    }

    protected function isValid($body) {
        return $body && is_array($body);
    }
} 