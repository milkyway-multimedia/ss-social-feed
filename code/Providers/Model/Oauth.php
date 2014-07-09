<?php namespace Milkyway\SocialFeed\Providers\Model;

use GuzzleHttp\Client;

/**
 * Milkyway Multimedia
 * HTTPProvider.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
abstract class Oauth extends HTTP {
    protected $credentials = [
        'consumer_key'    => '',
        'consumer_secret' => '',
        'token'           => '',
        'token_secret'    => '',
    ];

    protected function http()
    {
        parent::http();

        $this
            ->$client
            ->getEmitter()
            ->attach(Object::create('\GuzzleHttp\Subscriber\Oauth\Oauth1', $this->credentials));

        return $this->client;
    }

    protected function getHttpSettings() {
        return array_merge(parent::getHttpSettings(), [
                'defaults' => ['auth' => 'oauth']
        ]);
    }
} 