<?php namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\Oauth;
use Milkyway\SS\SocialFeed\Utilities;

/**
 * Milkyway Multimedia
 * Facebook.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Facebook extends Oauth {
    protected $endpoint = 'https://graph.facebook.com';
    protected $url = 'http://facebook.com';

    public function all($settings = []) {
        $all = [];

        try {
            $body = $this->getBodyFromCache($this->endpoint($settings['username'], 'feed'), $settings);

            foreach($body['data'] as $post) {
                if($this->allowed($post))
                    $all[] = $this->handlePost($post, $settings);
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

    protected function handlePost(array $data, $settings = []) {
        list($userId, $id) = explode('_', $data['id']);

        if(isset($settings['username']))
            $userId = $settings['username'];

        $post = [
            'ID' => $id,
            'Link' => \Controller::join_links($this->url . '/' . $userId . '/posts/' . $id),
            'Author' => isset($data['from']) && isset($data['from']['name']) ? $data['from']['name'] : '',
            'AuthorID' => isset($data['from']) && isset($data['from']['id']) ? $data['from']['id'] : '',
            'AuthorURL' => isset($data['from']) && isset($data['from']['id']) ? \Controller::join_links($this->url, $data['from']['id']) : '',
            'Avatar' => isset($data['from']) && isset($data['from']['id']) ? \Controller::join_links($this->endpoint, $data['from']['id'], 'picture') : '',
            'Content' => isset($data['message']) ? $this->textParser()->text($data['message']) : '',
            'Picture' => isset($data['picture']) ? str_replace(['/v/', 'p130x130/', 's130x130/', 'p50x50/', 's50x50/'], ['/', '', '', '', ''], $data['picture']) : '',
            'Thumbnail' => isset($data['picture']) ? $data['picture'] : '',
            'ObjectName' => isset($data['name']) ? $data['name'] : '',
            'ObjectURL' => isset($data['link']) ? $data['link'] : '',
            'Description' => isset($data['description']) ? $this->textParser()->text($data['description']) : '',
            'Icon' => isset($data['icon']) ? $data['icon'] : '',
            'Type' => isset($data['type']) ? $data['type'] : '',
            'StatusType' => isset($data['status_type']) ? $data['status_type'] : '',
            'Priority' => isset($data['created_time']) ? strtotime($data['created_time']) : 0,
            'Posted' => isset($data['created_time']) ? \DBField::create_field('SS_Datetime', $data['created_time']) : null,
            'LikesCount' => isset($data['likes']) && isset($data['likes']['data']) ? count($data['likes']['data']) : 0,
            'CommentsCount' => isset($data['comments']) && isset($data['comments']['data']) ? count($data['comments']['data']) : 0,
        ];

        $post['Created'] = $post['Posted'];
        $post['StyleClasses'] = $post['StatusType'];

        $post['LikesDescriptor'] = $post['LikesCount'] == 1 ? _t('SocialFeed.LIKE', 'like') : _t('SocialFeed.LIKES', 'likes');
        $post['CommentsDescriptor'] = $post['CommentsCount'] == 1 ? _t('SocialFeed.COMMENT', 'comment') : _t('SocialFeed.COMMENTS', 'comments');

        if (!$post['Content'] && isset($data['story']) && $data['story'])
            $post['Content'] = '<p>' . $this->textParser()->text($data['story']) . '</p>';

        if (isset($data['likes']) && isset($data['likes']['data']) && count($data['likes']['data'])) {
            $post['Likes'] = [];

            foreach ($data['likes']['data'] as $likeData) {
                $post['Likes'][] = [
                    'Author' => isset($likeData['name']) ? $likeData['name'] : '',
                    'AuthorID' => isset($likeData['id']) ? $likeData['id'] : '',
                    'AuthorURL' => isset($likeData['id']) ? \Controller::join_links($this->url, $likeData['id']) : '',
                ];
            }
        }

        if (isset($data['comments']) && isset($data['comments']['data']) && count($data['comments']['data'])) {
            $post['Comments'] = [];

            foreach ($data['comments']['data'] as $commentData) {
                $comment = array(
                    'Author' => isset($commentData['from']) && isset($commentData['from']['name']) ? $commentData['from']['name'] : '',
                    'AuthorID' => isset($commentData['from']) && isset($commentData['from']['id']) ? $commentData['from']['id'] : '',
                    'AuthorURL' => isset($commentData['from']) && isset($commentData['from']['id']) ? \Controller::join_links($this->url, $commentData['from']['id']) : '',
                    'Content' => isset($commentData['message']) ? $commentData['message'] : '',
                    'Posted' => isset($commentData['created_time']) ? \DBField::create_field('SS_Datetime', $commentData['created_time']) : null,
                    'ReplyByPoster' => isset($commentData['from']) && isset($commentData['from']['id']) ? $commentData['from']['id'] == $post['AuthorID'] : false,
                    'Likes' => isset($commentData['user_likes']) ? $commentData['user_likes'] : false,
                    'LikesCount' => isset($commentData['like_count']) ? count($commentData['like_count']) : 0,
                );

                $comment['LikesDescriptor'] = $comment['LikesCount'] == 1 ? _t('SocialFeed.LIKE', 'like') : _t('SocialFeed.LIKES', 'likes');
                $post['Comments'][] = $comment;
            }
        }

        return $post;
    }

    protected function allowed(array $post) {
        if (isset($post['is_hidden']) && $post['is_hidden'])
            return false;

        return true;
    }

    protected function endpoint($username, $type = 'feed') {
        return \Controller::join_links($this->endpoint, $username, $type);
    }

    protected function isValid($body) {
        return $body && is_array($body) && count($body) && isset($body['data']);
    }
}