<?php namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\HTTP;
use Milkyway\SS\SocialFeed\Utilities;

/**
 * Milkyway Multimedia
 * GooglePlus.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class GooglePlus extends HTTP {
    const VERSION = 'v1';

    protected $endpoint = 'https://www.googleapis.com/plus';
    protected $url = 'http://+.google.com';
    protected $type = 'people';

    public function all($settings = []) {
        $all = [];

        try {
            $body = $this->getBodyFromCache($this->endpoint($settings['username']), $settings);

            foreach($body['items'] as $post) {
                $all[] = $this->handlePost($post, $settings);
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

    protected function handlePost(array $data, $settings = []) {
        $post = array(
            'ID' => isset($data['id']) ? $data['id'] : 0,
            'Link' => isset($data['url']) ? $data['url'] : '',
            'Author' => isset($data['actor']) && isset($data['actor']['displayName']) ? $data['actor']['displayName'] : isset($settings['username']) ? $settings['username'] : '',
            'AuthorID' => isset($data['actor']) && isset($data['actor']['id']) ? $data['actor']['id'] : 0,
            'AuthorURL' => isset($data['actor']) && isset($data['actor']['url']) ? $data['actor']['url'] : '',
            'Avatar' => isset($data['actor']) && isset($data['actor']['image']) && isset($data['actor']['image']['url']) ? $data['actor']['image']['url'] : '',
            'Title' => isset($data['title']) ? Utilities::auto_link_text(nl2br($data['title'])) : '',
            'Type' => isset($data['object']) && isset($data['object']['objectType']) ? $data['object']['objectType'] : '',
            'Content' => isset($data['object']) && isset($data['object']['content']) ? '<p>' . Utilities::auto_link_text(nl2br($data['object']['content'])) . '</p>' : '',
            'ReplyCount' => isset($data['object']) && isset($data['object']['replies']) && isset($data['object']['replies']['totalItems']) ? $data['object']['replies']['totalItems'] : 0,
            'LikesCount' => isset($data['object']) && isset($data['object']['plusoners']) && isset($data['object']['plusoners']['totalItems']) ? $data['object']['plusoners']['totalItems'] : 0,
            'ReshareCount' => isset($data['object']) && isset($data['object']['resharers']) && isset($data['object']['resharers']['totalItems']) ? $data['object']['resharers']['totalItems'] : 0,
            'Priority' => isset($data['published']) ? strtotime($data['published']) : 0,
            'Posted' => isset($data['published']) ? DBField::create_field('SS_Datetime', strtotime($data['published'])) : null,
        );

        $post['Created'] = $post['Posted'];
        $post['StyleClasses'] = $post['Type'];

        $post['ReplyDescriptor'] = $post['ReplyCount'] == 1 ? _t('SocialFeed.REPLY', 'Reply') : _t('SocialFeed.REPLIES', 'Replies');
        $post['LikesDescriptor'] = $post['LikesCount'] == 1 ? _t('SocialFeed.PLUS_ONE', 'Plus One') : _t('SocialFeed.PLUS_ONES', 'Plus Ones');
        $post['ReshareDescriptor'] = $post['ReshareCount'] == 1 ? _t('SocialFeed.RESHARE', 'Reshare') : _t('SocialFeed.RESHARES', 'Reshares');

        if(isset($data['object']) && isset($data['object']['attachments']) && count($data['object']['attachments'])) {
            $post['Attachments'] = [];

            foreach ($data['object']['attachments'] as $attachment) {
                $post['Attachments'][] = [
                    'ID' => isset($attachment['id']) ? $attachment['id'] : '',
                    'Type' => isset($attachment['objectType']) ? $attachment['objectType'] : '',
                    'Link' => isset($attachment['url']) ? $attachment['url'] : '',
                    'Content' => isset($attachment['content']) ? $attachment['content'] : '',
                    'Picture' => isset($attachment['image']) && isset($attachment['image']['url']) ? $attachment['image']['url'] : '',
                    'Username' => isset($attachment['screen_name']) ? $attachment['screen_name'] : '',
                    'Name' => isset($attachment['name']) ? $attachment['name'] : '',
                ];
            }
        }

        return $post;
    }

    protected function endpoint($username, $type = 'activities') {
        return \Controller::join_links($this->endpoint, static::VERSION, $this->type, $username, $type, 'public');
    }

    protected function isValid($body) {
        return $body && is_array($body) && count($body) && isset($body['items']);
    }
} 