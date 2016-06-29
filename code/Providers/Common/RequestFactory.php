<?php namespace Milkyway\SS\SocialFeed\Providers\Common;

/**
 * Milkyway Multimedia
 * RequestFactory.php
 *
 * Special version that adds a specific header to oauth2 requests to make sure they send as async
 *
 * @package milkyway-multimedia/milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

if(!class_exists('League\OAuth2\Client\Tool\RequestFactory')) {
    return;
}

use League\OAuth2\Client\Tool\RequestFactory as Original;
use GuzzleHttp\Psr7\Request;

class RequestFactory extends Original
{
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        if($method == 'GET') {
            $headers['send_as_async'] = true;
        }

        return new Request($method, $uri, $headers, $body, $version);
    }
}