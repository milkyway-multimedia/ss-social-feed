<?php namespace Milkyway\SS\SocialFeed\Providers\Common;

/**
 * Milkyway Multimedia
 * Client.php
 *
 * THis is a custom client that will automatically register Oauth1 Subscribers
 * and send requests as async with specific options/header specified
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use GuzzleHttp\Client as Original;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PublicCacheStrategy;
use Milkyway\SS\SocialFeed\Cache\Silverstripe;
use Milkyway\SS\SocialFeed\Contracts\RequiresOauth1;
use Psr\Http\Message\RequestInterface;

class Client extends Original  {
    public function __construct(array $config = []) {
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }

        // Add cache strategy by default
        $config['handler']->push(new CacheMiddleware(new PublicCacheStrategy(new Silverstripe)), 'cache');

        if(isset($config['providers'])) {
            foreach((array)$config['providers'] as $id => $provider) {
                if($provider instanceof RequiresOauth1) {
                    if(!class_exists('GuzzleHttp\Subscriber\Oauth\Oauth1')) {
                        user_error('Please install guzzlehttp/oauth-subscriber to use this provider: ' . get_class($provider));
                    }

                    $config['handler']->push(new Oauth1(array_merge($provider->getOauth1Configuration()), [
                        'oauth_signature' => 'oauth-' . $id,
                    ]), 'oauth-' . $id);
                }

                $provider->setClient($this);
            }
        }

        parent::__construct($config);
    }

    public function send(RequestInterface $request, array $options = [])
    {
        if(!empty($options['send_as_async']) || !empty($request->getHeader('send_as_async'))) {
            if(!empty($options['send_as_async'])) {
                unset($options['send_as_async']);
            }
            else {
                $request = $request->withoutHeader('send_as_async');
            }

            return $this->sendAsync($request, $options);
        }

        return parent::send($request, $options);
    }
}