<?php namespace Milkyway\SS\SocialFeed;
/**
 * Milkyway Multimedia
 * Collector.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Collector {
    /** @var \ArrayAccess A list of profiles */
    protected $profiles;

    /** @var int Hours to cache */
    protected $cache = 6;

    /** @var string Sorting of list */
    protected $sort = 'Priority DESC';

    public function __construct(\Countable $profiles, $cache = 6, $sort = 'Priority DESC') {
        $this->profiles = $profiles;
        $this->cache = $cache;
        $this->sort = $sort;
    }

    public function all() {
        $feed = \ArrayList::create();

        if($this->profiles->count()) {
            foreach($this->profiles as $profile)
                $feed->merge($this->collect($profile));

            if($this->sort) {
                if(is_string($this->sort) && strpos($this->sort, ' ') !== false) {
                    $sort = explode(' ', $this->sort);
                    $feed = $feed->sort($sort[0], $sort[1]);
                }
                else
                    $feed = $feed->sort($this->sort);
            }
            else
                $feed = $feed->sort('Priority', 'DESC');
        }

        return $feed;
    }

    public function collect($profile) {
        $feed = [];

        $provider = \Object::create($profile->Provider, $this->cache, (array) $profile->OauthConfiguration);

	    if($profile->RequiresExtendedPermissions)
			$provider = $provider->extendedPermissions($profile->RequiresExtendedPermissions);

        $template = $profile->Templates;
        $posts = $provider->all((array) $profile->FeedSettings);
        $postSettings = (array) $profile->PostSettings;

        foreach($posts as $post) {
            if(!($post instanceof \ViewableData)) {
                $post = $profile->processPost(array_merge($postSettings, $post));
                $post['Profile'] = $profile;
                $this->convertToArrayData($post);
                $post['forTemplate'] = \ArrayData::create($post)->renderWith($template);
                $feed[] = \ArrayData::create($post);
            }
            else {
                $profile->processPost($postSettings, $post);

                foreach($postSettings as $setting => $value)
                    $post->$setting = $value;

                $post->Profile = $profile;
                $post->forTemplate = $post->renderWith($template);
                $feed[] = $post;
            }
        }

        return $feed;
    }

    protected function convertToArrayData(&$data) {
        foreach($data as $key => $item) {
            if(is_array($item)) {
                if(!\ArrayLib::is_associative($item)) {
                    $this->convertToArrayData($item);
                    $data[$key] = \ArrayList::create($item);
                }
                else
                    $data[$key] = \ArrayData::create($item);
            }
        }
    }
} 