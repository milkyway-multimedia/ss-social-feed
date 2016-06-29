<?php namespace Milkyway\SS\SocialFeed\Providers\Common;

/**
 * Milkyway Multimedia
 * Oauth1.php
 *
 * Special version that will allow you to use a new auth signature
 * Allowing us to have multiple oauth signatures and async clients
 *
 * @package milkyway-multimedia/milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

if(!class_exists('GuzzleHttp\Subscriber\Oauth\Oauth1')) {
    return;
}

use GuzzleHttp\Subscriber\Oauth\Oauth1 as Original;

class Oauth1 extends Original
{
    protected $oauthSignature;

    public function __construct($config)
    {
        if(!empty($config['oauth_signature'])) {
            $this->oauthSignature = $config['oauth_signature'];
        }

        parent::__construct($config);
    }

    public function __invoke(callable $handler)
    {
        $original = parent::__invoke($handler);

        return function ($request, array $options) use ($handler, $original) {
            if (isset($options['auth']) && $options['auth'] != $this->oauthSignature) {
                return $handler($request, $options);
            }

            $options['auth'] = 'oauth';

            return $original($request, $options);
        };
    }
}