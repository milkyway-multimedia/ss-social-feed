<?php namespace Milkyway\SS\SocialFeed\Contracts;

/**
 * Milkyway Multimedia
 * HttpProvider.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpProvider {
    public function setClient(ClientInterface $client);

    public function parseResponse(ResponseInterface $response, $settings = []);
} 