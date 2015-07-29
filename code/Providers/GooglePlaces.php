<?php namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\HTTP;

/**
 * Milkyway Multimedia
 * GooglePlaces.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class GooglePlaces extends HTTP {
    protected $endpoint = 'https://maps.googleapis.com/maps/api/place/details/json';
    protected $url = 'http://+.google.com';

    public function all($settings = []) {
        $all = [];

        try {
            $body = $this->getBodyFromCache($this->endpoint(), $settings);

            if(!isset($body['result']['reviews']))
                return $all;

            foreach($body['result']['reviews'] as $id => $post) {
	            $post['id'] = $id;
	            $post['url'] = isset($body['result']['url']) ? $body['result']['url'] : '';
	            $post['place_id'] = isset($body['result']['place_id']) ? $body['result']['place_id'] : '';
	            $post['overall_rating'] = isset($body['result']['rating']) ? $body['result']['rating'] : 2.5;
                $all[] = $this->handlePost($post, $settings);
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }

	public function textSearch($text = '', $settings = []) {
		$all = [];

		try {
			$settings['query']['query'] = $text;
			$body = $this->getBodyFromCache($this->endpoint('textsearch'), $settings);
			$all = $body['results'];
		} catch (\Exception $e) {
			\Debug::show($e->getMessage());
		}

		return $all;
	}

    public function details($settings = []) {
        $body = [];

        try {
            $body = $this->getBodyFromCache($this->endpoint(), $settings);

            if(isset($body['result'])) {
                $body = $body['result'];
            }
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $body;
    }

    protected function handlePost(array $data, $settings = []) {
        $post = array(
            'ID' => isset($data['id']) ? $data['id'] : 0,
            'OverallRating' => isset($data['overall_rating']) ? $data['overall_rating'] : 2.5,
            'Rating' => isset($data['rating']) ? $data['rating'] : 0,
            'Link' => isset($data['url']) ? $data['url'] : '',
            'Language' => isset($data['language']) ? $data['language'] : '',
            'Author' => isset($data['author_name']) ? $data['author_name'] : '',
            'AuthorURL' => isset($data['author_url']) ? $data['author_url'] : '',
            'Content' => isset($data['text']) ? '<p>' . $this->textParser()->text($data['text']) . '</p>' : '',
            'Priority' => isset($data['time']) ? $data['time'] : 0,
            'Posted' => isset($data['time']) ? \DBField::create_field('SS_Datetime', $data['time']) : null,
        );

        $post['Created'] = $post['Posted'];

        return $post;
    }

    protected function endpoint($action = '') {
        return $action ? str_replace('/details/', '/'.$action.'/', $this->endpoint) : $this->endpoint;
    }

    protected function isValid($body) {
        return $body && is_array($body) && count($body) && (isset($body['results']) || (isset($body['result'])));
    }
} 
