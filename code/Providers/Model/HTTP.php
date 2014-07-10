<?php namespace Milkyway\SocialFeed\Providers\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Milkyway\SocialFeed\Contracts\Provider;

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

    protected function isError(Response $response) {
        return ($response->getStatusCode() < 200 || $response->getStatusCode() > 399);
    }
}