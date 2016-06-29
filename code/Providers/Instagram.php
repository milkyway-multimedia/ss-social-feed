<?php namespace Milkyway\SS\SocialFeed\Providers;

/**
 * Milkyway Multimedia
 * Facebook.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\SocialFeed\Providers\Model\Oauth2;
use Psr\Http\Message\ResponseInterface;

class Instagram extends Oauth2
{
    const VERSION = 'v1';

    protected $endpoint = 'https://api.instagram.com';
    protected $url = 'http://instagram.com';

    protected $providerClass = 'League\OAuth2\Client\Provider\Instagram';

    protected $defaultType = 'recent';

    public function all($settings = [])
    {
        $type = isset($settings['type']) ? $settings['type'] : $this->defaultType;
        $user = !empty($settings['username']) ? 'users/' . $settings['username'] : '';
        return $this->request($this->endpoint($user, $type), $settings);
    }

    public function parseResponse(ResponseInterface $response, $settings = [])
    {
        $body = parent::parseResponse($response, $settings);

        $all = [];
        $type = empty($settings['type']) ? $this->defaultType : $settings['type'];

        if (!isset($body['data'])) {
            $body['data'] = [];
        }

        foreach ($body['data'] as $key => $post) {
            if (!$this->allowed($post)) {
                continue;
            }

            if (empty($post['id'])) {
                $post['id'] = is_numeric($key) ? $key + 1 : $key;
            }

            $all[] = $this->handlePost($post, $settings, $type, $settings['username']);
        }

        return $all;
    }

    protected function handlePost(array $data, $settings = [], $type = 'feed', $userId = '')
    {
        if (strpos($data['id'], '_') !== false) {
            list($userId, $id) = explode('_', $data['id']);
        } else {
            $id = $data['id'];
        }

        $post = [
            'ID'            => $id,
            'Link'          => isset($data['link']) ? $data['link'] : '',
            'Author'        => isset($data['user']) && isset($data['user']['username']) ? $data['user']['username'] : '',
            'AuthorID'      => isset($data['user']) && isset($data['user']['id']) ? $data['user']['id'] : '',
            'AuthorURL'     => isset($data['user']) && isset($data['user']['username']) ? \Controller::join_links($this->url,
                $data['user']['username']) : \Controller::join_links($this->url, $userId),
            'Avatar'        => isset($data['user']) && isset($data['user']['profile_picture']) ? $data['user']['profile_picture'] : '',
            'Content'       => isset($data['caption']) && isset($data['caption']['text']) ? $this->textParser()->text($data['caption']['text']) : '',
            'Picture'       => isset($data['images']) && isset($data['images']['standard_resolution']) ? $data['images']['standard_resolution']['url'] : '',
            'PictureWidth'  => isset($data['images']) && isset($data['images']['standard_resolution']) ? $data['images']['standard_resolution']['width'] : '',
            'PictureHeight' => isset($data['images']) && isset($data['images']['standard_resolution']) ? $data['images']['standard_resolution']['height'] : '',
            'Thumbnail'     => isset($data['images']) && isset($data['images']['thumbnail']) ? $data['images']['thumbnail'] : '',
            'ObjectURL'     => isset($data['videos']) && isset($data['videos']['standard_resolution']) ? $data['videos']['standard_resolution']['url'] : '',
            'ObjectWidth'   => isset($data['videos']) && isset($data['videos']['standard_resolution']) ? $data['videos']['standard_resolution']['width'] : '',
            'ObjectHeight'  => isset($data['videos']) && isset($data['videos']['standard_resolution']) ? $data['videos']['standard_resolution']['height'] : '',
            'Type'          => isset($data['type']) ? $data['type'] : '',
            'Priority'      => isset($data['created_time']) ? strtotime($data['created_time']) : 0,
            'Posted'        => isset($data['created_time']) ? \DBField::create_field('SS_Datetime',
                $data['created_time']) : null,
            'LikesCount'    => isset($data['likes']) && isset($data['likes']['count']) ? $data['likes']['count'] : 0,
            'CommentsCount' => isset($data['comments']) && isset($data['comments']['count']) ? $data['comments']['count'] : 0,
        ];

        $post['Created'] = $post['Posted'];
        $post['StyleClasses'] = $post['Type'];

        if (!empty($data['likes'])) {
            $post['LikesDescriptor'] = $post['LikesCount'] == 1 ? _t('SocialFeed.LIKE', 'like') : _t('SocialFeed.LIKES',
                'likes');
        }

        if (!empty($data['comments'])) {
            $post['CommentsDescriptor'] = $post['CommentsCount'] == 1 ? _t('SocialFeed.COMMENT',
                'comment') : _t('SocialFeed.COMMENTS', 'comments');
        }

        return $post;
    }

    protected function endpoint($username = '', $type = '')
    {
        return \Controller::join_links($this->endpoint, static::VERSION, $username, $type);
    }

    protected function isValid($body)
    {
        return $body && is_array($body) && !empty($body) && !empty($body['data']);
    }
}