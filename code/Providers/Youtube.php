<?php namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\HTTP;

/**
 * Milkyway Multimedia
 * Youtube.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Youtube extends HTTP {
    const VERSION = 'v3';

    protected $endpoint = 'https://www.googleapis.com/youtube/';
    protected $url = 'http://youtube.com';

    public function all($settings = []) {
        $all = [];

        try {
            $body = $this->getBodyFromCache($this->endpoint('search'), $settings);

            foreach($body['items'] as $post) {
                $all[] = $this->handlePost($post, $settings);
            }

            if(isset($settings['maxResults'])) {
                while(count($all) < $settings['maxResults'] && isset($body['nextPageToken']) && $body['nextPageToken']) {
                    $settings['pageToken'] = $body['nextPageToken'];
                    $body = $this->getBodyFromCache($this->endpoint('search'), $settings);

                    foreach($body['items'] as $post) {
                        $all[] = $this->handlePost($post, $settings);
                    }
                }
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

    public function channelSearch($username, $settings = []) {
        $all = [];

        try {
            $settings['query']['forUsername'] = $username;

            if(!isset($settings['query']) || !isset($settings['query']['part']))
                $settings['query']['part'] = 'id';

            $body = $this->getBodyFromCache($this->endpoint('channels'), $settings);
            $all = $body['items'];
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

    protected function handlePost(array $data, $settings = []) {
        $post = [
            'ID' => isset($data['id']) && isset($data['id']['videoId']) ? $data['id']['videoId'] : '0',
            'Author' => isset($data['snippet']) && isset($data['snippet']['channelTitle']) ? \FormField::name_to_label($data['snippet']['channelTitle']) : '',
            'AuthorID' => isset($data['snippet']) && isset($data['snippet']['channelId']) ? $data['snippet']['channelId'] : 0,
            'AuthorURL' => isset($data['snippet']) && isset($data['snippet']['channelId']) ? \Controller::join_links($this->url, 'channel', $data['snippet']['channelId']) : '',

            'Title'       => isset($data['snippet']) && isset($data['snippet']['title']) ? $data['snippet']['title']: '',
            'Content'   => isset($data['snippet']) && isset($data['snippet']['description']) ? $this->textParser()->text($data['snippet']['description']) : '',

            'Priority' => isset($data['snippet']) && isset($data['snippet']['publishedAt']) ? strtotime($data['snippet']['publishedAt']) : 0,
            'Posted' => isset($data['snippet']) && isset($data['snippet']['publishedAt']) ? \DBField::create_field('SS_Datetime', strtotime($data['snippet']['publishedAt'])) : null,
        ];

        if(isset($data['snippet']) && isset($data['snippet']['thumbnails'])) {
            if(isset($data['snippet']['thumbnails']['high']) && isset($data['snippet']['thumbnails']['high']['url']))
                $post['Cover'] = $data['snippet']['thumbnails']['high']['url'];
            else if(isset($data['snippet']['thumbnails']['medium']) && isset($data['snippet']['thumbnails']['medium']['url']))
                $post['Cover'] = $data['snippet']['thumbnails']['medium']['url'];
            else if(isset($data['snippet']['thumbnails']['default']) && isset($data['snippet']['thumbnails']['default']['url']))
                $post['Cover'] = $data['snippet']['thumbnails']['default']['url'];
        }

        if($post['ID']) {
            $params = (array)singleton('env')->get('Youtube.video_params');

            if(isset($settings['videoParams']))
                $params = array_merge($params, (array)$settings['videoParams']);

            $params['v'] = $post['ID'];
            $post['Link'] = \Controller::join_links($this->url, 'watch', '?' . http_build_query($params));
            $this->setFromEmbed($post);
        }

        if(isset($post['ObjectDescription']) && $post['ObjectDescription'] == $post['Content'])
            unset($post['ObjectDescription']);

        if(isset($post['Description']) && $post['Description'] == $post['Content'])
            unset($post['Description']);

        return $post;
    }

    protected function endpoint($type = 'search') {
        return \Controller::join_links($this->endpoint, static::VERSION, $type);
    }

    protected function isValid($body) {
        return $body && is_array($body) && count($body) && isset($body['items']);
    }
}