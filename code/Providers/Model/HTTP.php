<?php namespace Milkyway\SocialFeed\Providers\Model;

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
            $this->client = Object::create('\GuzzleHttp\Client', $this->getHttpSettings());

        return $this->client;
    }

    protected function getHttpSettings() {
        return [
            'base_url' => $this->endpoint,
        ];
    }
} 