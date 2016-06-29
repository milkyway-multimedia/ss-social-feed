<?php namespace Milkyway\SS\SocialFeed\Providers;

/**
 * Milkyway Multimedia
 * GooglePlaces.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\SocialFeed\Providers\Model\HTTP;
use Psr\Http\Message\ResponseInterface;

class GooglePlaces extends HTTP
{
    protected $endpoint = 'https://maps.googleapis.com/maps/api/place/details/json';
    protected $url = 'http://+.google.com';

    public function all($settings = [])
    {
        return $this->request($this->endpoint(), $settings);
    }

    public function parseResponse(ResponseInterface $response, $settings = [])
    {
        $body = parent::parseResponse($response, $settings);

        $all = [];

        if (!isset($body['result']['reviews'])) {
            return $all;
        }

        foreach ($body['result']['reviews'] as $id => $post) {
            $post['id'] = $id;
            $post['url'] = isset($body['result']['url']) ? $body['result']['url'] : '';
            $post['place_id'] = isset($body['result']['place_id']) ? $body['result']['place_id'] : '';
            $post['overall_rating'] = isset($body['result']['rating']) ? $body['result']['rating'] : 2.5;

            $all[] = $this->handlePost($post, $settings);
        }

        return $all;
    }

    public function textSearch($text = '', $settings = [])
    {
        try {
            $settings['query']['query'] = $text;
            return $this->request($this->endpoint('textsearch'), $settings)->wait()['results'];
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return [];

    }

    public function details($settings = [])
    {
        try {
            return $this->request($this->endpoint(), $settings)->wait()['results'];
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return [];
    }

    protected function handlePost(array $data, $settings = [])
    {
        $post = [
            'ID'            => isset($data['id']) ? $data['id'] : 0,
            'OverallRating' => isset($data['overall_rating']) ? $data['overall_rating'] : 2.5,
            'Rating'        => isset($data['rating']) ? $data['rating'] : 0,
            'Link'          => isset($data['url']) ? $data['url'] : '',
            'Language'      => isset($data['language']) ? $data['language'] : '',
            'Author'        => isset($data['author_name']) ? $data['author_name'] : '',
            'AuthorURL'     => isset($data['author_url']) ? $data['author_url'] : '',
            'Content'       => isset($data['text']) ? '<p>' . $this->textParser()->text($data['text']) . '</p>' : '',
            'Priority'      => isset($data['time']) ? $data['time'] : 0,
            'Posted'        => isset($data['time']) ? \DBField::create_field('SS_Datetime', $data['time']) : null,
        ];

        $post['Created'] = $post['Posted'];

        return $post;
    }

    protected function endpoint($action = '')
    {
        return $action ? str_replace('/details/', '/' . $action . '/', $this->endpoint) : $this->endpoint;
    }

    protected function isValid($body)
    {
        return $body && is_array($body) && !empty($body) && !empty($body['results']);
    }
}
