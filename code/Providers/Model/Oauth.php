<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

use GuzzleHttp\Subscriber\Oauth\Oauth1;

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

    public function __construct($cache = 6, array $credentials = []) {
        parent::__construct($cache);
        $this->credentials = $credentials;
    }

    protected function http()
    {
        parent::http();

        $this
            ->client
            ->getEmitter()
            ->attach(new Oauth1($this->credentials));

        return $this->client;
    }

    protected function getHttpSettings() {
        return array_merge(parent::getHttpSettings(), [
                'defaults' => ['auth' => 'oauth']
        ]);
    }
} 