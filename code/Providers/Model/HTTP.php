<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

/**
 * Milkyway Multimedia
 * HTTP.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use GuzzleHttp\ClientInterface;
use Milkyway\SS\SocialFeed\Contracts\HttpProvider;
use Milkyway\SS\SocialFeed\Contracts\Provider;
use Psr\Http\Message\ResponseInterface;
use Oembed;

abstract class HTTP implements Provider, HttpProvider
{
    protected $endpoint = '';

    protected $configuration;
    protected $client;
    protected $textParser;

    protected $embedWillBeAjax = false;

    public function __construct($configuration = [])
    {
        $this->configuration = $configuration;
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    public function parseResponse(ResponseInterface $response, $settings = [])
    {
        if ($response && $this->isError($response)) {
            throw new HTTP_Exception($response,
                sprintf('Response returned an error: %s.', $this->endpoint));
        }

        $body = $this->parseRawResponse($response);

        if (!$this->isValid($body)) {
            throw new HTTP_Exception($response,
                sprintf('Data not received correctly from %s.', $this->endpoint));
        }

        return $body;
    }

    public function all($settings = [])
    {
        return $this->request($this->endpoint, $settings);
    }

    protected function request($url, $settings = []) {
        $response = $this->client->getAsync($url, $settings);

        return $response instanceof ResponseInterface ? $this->parseResponse($response, $settings) : $response;
    }

    protected function isError(ResponseInterface $response)
    {
        return ($response->getStatusCode() < 200 || $response->getStatusCode() > 399);
    }

    protected function parseRawResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_BIGINT_AS_STRING);
    }

    protected function isValid($body)
    {
        return true;
    }

    protected function textParser()
    {
        if (!$this->textParser) {
            $this->textParser = \Injector::inst()->create('Milkyway\SS\SocialFeed\Contracts\TextParser');
        }

        return $this->textParser;
    }

    protected function setFromEmbed(&$post)
    {
        if (!isset($post['Link']) || $this->embedWillBeAjax) {
            return;
        }

        $record = [];

        if (class_exists('Embed\Embed')) {
            $info = \Embed\Embed::create($post['Link'], singleton('env')->get('SocialFeed.embed_configuration', [
                'resolver' => [
                    'class' => 'Embed\\RequestResolvers\\Guzzle5',

                    'config' => [
                        'client' => $this->client,
                    ]
                ],
            ]));

            $record['ObjectName'] = $info->getTitle();
            $record['ObjectURL'] = $info->getUrl();
            $record['ObjectWidth'] = $info->getWidth();
            $record['ObjectHeight'] = $info->getHeight();
            $record['ObjectThumbnail'] = $info->getImage();
            $record['ObjectDescription'] = $info->getDescription();
            $record['ObjectType'] = $info->getType();
            $record['ObjectEmbed'] = $info->getCode();
        } else {
            if ($info = Oembed::get_oembed_from_url($post['Link'])) {
                if ($info->hasField('title')) {
                    $record['ObjectName'] = $info->getField('title');
                }

                if ($info->hasField('url')) {
                    $record['ObjectURL'] = $info->getField('url');
                }

                if ($info->hasField('width')) {
                    $record['ObjectWidth'] = $info->getField('width');
                }

                if ($info->hasField('height')) {
                    $record['ObjectHeight'] = $info->getField('height');
                }

                if ($info->hasField('thumbnail')) {
                    $record['ObjectThumbnail'] = $info->getField('thumbnail');
                }

                if ($info->hasField('description')) {
                    $record['ObjectDescription'] = $this->textParser()->text($info->getField('description'));
                }

                if ($info->hasField('type')) {
                    $record['ObjectType'] = $info->getField('type');
                }

                $record['ObjectEmbed'] = $info->forTemplate();
            }
        }

        foreach ($record as $key => $value) {
            $post[$key] = $value;
        }
    }

    protected function mergeSettingsRecursively()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        if(!is_array($base)) $base = empty($base) ? array() : array($base);
        foreach($arrays as $append) {
            if(!is_array($append)) $append = array($append);
            foreach($append as $key => $value) {
                if(!array_key_exists($key, $base) and !is_numeric($key)) {
                    $base[$key] = $append[$key];
                    continue;
                }
                if(is_array($value) or is_array($base[$key])) {
                    $base[$key] = $this->mergeSettingsRecursively($base[$key], $append[$key]);
                }
                else if(is_numeric($key))
                {
                    if(!in_array($value, $base)) $base[] = $value;
                }
                else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }
}

class HTTP_Exception extends \Exception
{
    public $response;

    public function __construct($response = null, $message = null, $statusCode = null, $statusDescription = null)
    {
        parent::__construct($message, $statusCode, $statusDescription);
        $this->response = $response;
    }
}