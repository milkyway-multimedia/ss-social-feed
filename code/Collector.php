<?php namespace Milkyway\SS\SocialFeed;

/**
 * Milkyway Multimedia
 * Collector.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use GuzzleHttp\Promise\PromiseInterface;
use Milkyway\SS\SocialFeed\Contracts\HttpProvider;
use Milkyway\SS\SocialFeed\Contracts\RequiresOauth2;
use Object;
use ArrayList;

class Collector
{
    /** @var \Countable A list of profiles */
    protected $profiles;

    /** @var \Object Parent of profiles, for config checking */
    protected $parent = null;

    /** @var int Limit before stopping the collecting */
    protected $limit = null;

    /** @var string Sorting of list */
    protected $sort = 'Priority DESC';

    /** @var string Prepend to templates */
    protected $templatePrepend = '';

    /** @var \GuzzleHttp\ClientInterface */
    protected $client;

    /** @var array Promises for us to unwrap */
    protected $promises;

    /** @var int Amount collected already */
    private $collected = 0;

    public function __construct(
        \Countable $profiles,
        $parent = null,
        $limit = null,
        $sort = 'Priority DESC',
        $templatePrepend = ''
    ) {
        $this->profiles = $profiles;
        $this->parent = $parent;
        $this->limit = $limit;
        $this->sort = $sort;
        $this->templatePrepend = $templatePrepend;
    }

    public function all()
    {
        $list = ArrayList::create();
        $feeds = [];

        if (count($this->profiles)) {
            $providers = [];
            $hasHttpProvider = false;

            foreach ($this->profiles as $profile) {
                $providers[$profile->ID] = Object::create($profile->Provider, (array)$profile->OauthConfiguration);

                if (!$hasHttpProvider && $providers[$profile->ID] instanceof HttpProvider) {
                    $hasHttpProvider = true;
                }
            }

            if ($hasHttpProvider && !$this->client) {
                $this->client = Object::create('Milkyway\SS\SocialFeed\Providers\Common', [], $providers);
            }

            foreach ($this->profiles as $profile) {
                $profile->Parent = $this->parent;

                if ($this->isOverTheLimit()) {
                    break;
                }

                $feeds[$profile->ID] = $this->collect($profile, $providers[$profile->ID]);
            }

            if (!empty($this->promises)) {
                $responses = \GuzzleHttp\Promise\unwrap(array_column($this->promises, 'promise', 'id'));

                foreach($responses as $id => $response) {
                    $feeds[$id] = $this->processProvidedResponse($this->promises[$id]['provider']->parseResponse($response, $this->promises[$id]['settings']), $this->promises[$id]['profile']);
                }
            }

            $this->client = null;
            $this->promises = [];

            foreach ($feeds as $feed) {
                $list = $list->merge($feed);
            }

            if ($this->sort) {
                if (is_string($this->sort) && strpos($this->sort, ' ') !== false) {
                    $sort = explode(' ', $this->sort);
                    $list = $list->sort($sort[0], $sort[1]);
                } else {
                    $list = $list->sort($this->sort);
                }
            } else {
                $list = $list->sort('Priority', 'DESC');
            }
        }

        return $this->limit ? $list->limit($this->limit) : $list;
    }

    public function collect($profile, $provider = null)
    {
        if (!$provider) {
            $provider = Object::create($profile->Provider, (array)$profile->ProviderConfiguration);
        }

        if ($provider instanceof HttpProvider) {
            $provider->setClient($this->client);
        }

        $feedSettings = (array)$profile->getFeedSettings($this->parent);

        if (($provider instanceof RequiresOauth2) && !empty($profile->RequiresExtendedPermissions)) {
            $provider = $provider->extendedPermissions(array_merge($feedSettings, $profile->RequiresExtendedPermissions));
        }

        $posts = $provider->all($feedSettings);

        if ($posts instanceof PromiseInterface) {
            $this->promises[$profile->ID] = [
                'id'       => $profile->ID,
                'promise'  => $posts,
                'provider' => $provider,
                'profile'  => $profile,
                'settings' => $feedSettings,
            ];

            return true;
        }

        return $this->processProvidedResponse($posts, $profile);
    }

    public function prependTemplate($templatePrepend = '')
    {
        $this->templatePrepend = $templatePrepend;
        return $this;
    }

    protected function convertToArrayData(&$data)
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                if (!\ArrayLib::is_associative($item)) {
                    $this->convertToArrayData($item);
                    $data[$key] = \ArrayList::create($item);
                } else {
                    $data[$key] = \ArrayData::create($item);
                }
            }
        }
    }

    protected function isOverTheLimit()
    {
//		return $this->limit !== null && $this->collected >= $this->limit;
        return false;
    }

    protected function processProvidedResponse($posts, $profile)
    {
        $feed = [];

        $template = $this->templatePrepend ? array_merge(array_map(function ($template) {
            return $this->templatePrepend . '_' . $template;
        }, (array)$profile->Templates), (array)$profile->Templates) : $profile->Templates;

        $postSettings = (array)$profile->getPostSettings($this->parent);

        foreach ($posts as $post) {
            if ($this->isOverTheLimit()) {
                break;
            }

            if (!($post instanceof \ViewableData)) {
                $post = $profile->processPost(array_merge($postSettings, $post));

                if (isset($post['hidden']) && $post['hidden']) {
                    continue;
                }

                $post['Profile'] = $profile;
                $this->convertToArrayData($post);
                $post['forTemplate'] = \ArrayData::create($post)->renderWith($template);
                $feed[] = \ArrayData::create($post);
            } else {
                $profile->processPost($postSettings, $post);

                if (isset($post->hidden) && $post->hidden) {
                    continue;
                }

                foreach ($postSettings as $setting => $value) {
                    $post->$setting = $value;
                }

                $post->Profile = $profile;
                $post->forTemplate = $post->renderWith($template);
                $feed[] = $post;
            }

            $this->collected++;
        }

        return $feed;
    }
} 