<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use Milkyway\SS\SocialFeed\Contracts\Provider;

/**
 * Milkyway Multimedia
 * HTTP.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
abstract class HTTP implements Provider {
    protected $endpoint = '';
    protected $client;
    protected $cacheLifetime = 6;

    protected $cache;
    protected $textParser;

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
        return $response->json();
    }

    protected function isValid($body) {
        return true;
    }

    protected function textParser() {
        if(!$this->textParser)
            $this->textParser = \Injector::inst()->create('Milkyway\SS\SocialFeed\Contracts\TextParser');

        return $this->textParser;
    }
}

class HTTP_Exception extends \Exception {
    public $response;

    public function __construct($response = null, $message = null, $statusCode = null, $statusDescription = null) {
        parent::__construct($message, $statusCode, $statusDescription);
        $this->response = $response;
    }
}