<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

use GuzzleHttp\Client;
use Milkyway\SS\SocialFeed\Contracts\Provider;
use Psr\Http\Message\ResponseInterface;

/**
 * Milkyway Multimedia
 * HTTP.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
abstract class HTTP implements Provider {
    protected $endpoint = '';
    protected $client;
    protected $cacheLifetime = 6;

    protected $cache;
    protected $textParser;

    protected $embedWillBeAjax = true;

    public function __construct($cache = 6) {
        $this->cacheLifetime = $cache;
    }

    public function cleanCache() {
        $this->cache()->clean();
    }

    /**
     * Get a new HTTP client instance.
     * @return \GuzzleHttp\Client
     */
    protected function http()
    {
        if(!$this->client)
            $this->client = new Client($this->getHttpSettings());

        return $this->client;
    }

    protected function getHttpSettings() {
        return [
            'base_url' => $this->endpoint,
        ];
    }

    protected function isError(ResponseInterface $response) {
        return ($response->getStatusCode() < 200 || $response->getStatusCode() > 399);
    }

    protected function cache() {
        if(!$this->cache)
            $this->cache = \SS_Cache::factory('SocialFeed_Providers', 'Output', ['lifetime' => $this->cacheLifetime * 60 * 60]);

        return $this->cache;
    }

    protected function getCacheKey(array $vars = []) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', get_class($this) . '_' . urldecode(http_build_query($vars, '', '_')));
    }

    protected function getBodyFromCache($url, $settings = []) {
        $cacheKey = $this->getCacheKey($settings);

        if(!($body = unserialize($this->cache()->load($cacheKey)))) {
            $response = $this->http()->get(
                $url,
                [
                    'query' => isset($settings['query']) ? $settings['query'] : [],
                ]
            );

            if($response && !$this->isError($response)) {
                $body = $this->parseResponse($response);

                if(!$this->isValid($body))
                    throw new HTTP_Exception($response, sprintf('Data not received from %s. Please check your credentials.', $this->endpoint));

                $this->cache()->save(serialize($body), $cacheKey);

                return $body;
            }
        }

        return $body;
    }

    protected function parseResponse(ResponseInterface $response) {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_BIGINT_AS_STRING);
    }

    protected function isValid($body) {
        return true;
    }

    protected function textParser() {
        if(!$this->textParser)
            $this->textParser = \Injector::inst()->create('Milkyway\SS\SocialFeed\Contracts\TextParser');

        return $this->textParser;
    }

    protected function setFromEmbed(&$post) {
        if(!isset($post['Link']))
            return;

        $cacheKey = $this->getCacheKey(['embed' => $post['Link']]);

        if(!($record = unserialize($this->cache()->load($cacheKey)))) {
            $record = [];

            if (class_exists('Embed\Embed')) {
                $info = \Embed\Embed::create($post['Link']);

                $record['ObjectName'] = $info->getTitle();
                $record['ObjectURL'] = $info->getUrl();
                $record['ObjectWidth'] = $info->getWidth();
                $record['ObjectHeight'] = $info->getHeight();
                $record['ObjectThumbnail'] = $info->getImage();
                $record['ObjectDescription'] = $info->getDescription();
                $record['ObjectType'] = $info->getType();
                $record['ObjectEmbed'] = $info->getCode();
            } else if ($info = \Oembed::get_oembed_from_url($post['Link'])) {
                if ($info->hasField('title'))
                    $record['ObjectName'] = $info->getField('title');

                if ($info->hasField('url'))
                    $record['ObjectURL'] = $info->getField('url');

                if ($info->hasField('width'))
                    $record['ObjectWidth'] = $info->getField('width');

                if ($info->hasField('height'))
                    $record['ObjectHeight'] = $info->getField('height');

                if ($info->hasField('thumbnail'))
                    $record['ObjectThumbnail'] = $info->getField('thumbnail');

                if ($info->hasField('description'))
                    $record['ObjectDescription'] = $this->textParser()->text($info->getField('description'));

                if ($info->hasField('type'))
                    $record['ObjectType'] = $info->getField('type');

                $record['ObjectEmbed'] = $info->forTemplate();
            }

            $this->cache()->save(serialize($record), $cacheKey);
        }

        foreach($record as $key => $value) {
            $post[$key] = $value;
        }
    }
}

class HTTP_Exception extends \Exception {
    public $response;

    public function __construct($response = null, $message = null, $statusCode = null, $statusDescription = null) {
        parent::__construct($message, $statusCode, $statusDescription);
        $this->response = $response;
    }
}