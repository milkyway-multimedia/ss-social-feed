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

	/** @var \Object Parent of profiles, for config checking */
	protected $parent = null;

	/** @var int Limit before stopping the collecting */
	protected $limit = null;

    /** @var int Hours to cache */
    protected $cache = 6;

    /** @var string Sorting of list */
    protected $sort = 'Priority DESC';

    /** @var string Prepend to templates */
    protected $templatePrepend = '';

	/** @var int Amount collected already */
	private $collected = 0;

    public function __construct(\Countable $profiles, $parent = null, $limit = null, $cache = 6, $sort = 'Priority DESC', $templatePrepend = '') {
        $this->profiles = $profiles;
        $this->parent = $parent;
        $this->limit = $limit;
        $this->cache = $cache;
        $this->sort = $sort;
        $this->templatePrepend = $templatePrepend;
    }

    public function all() {
        $feed = \ArrayList::create();

        if($this->profiles->count()) {
            foreach($this->profiles as $profile) {
	            $profile->Parent = $this->parent;

	            if($this->isOverTheLimit())
		            break;

                $feed->merge($this->collect($profile));
            }

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

        $template = $this->templatePrepend ? array_merge(array_map(function($template) {
            return $this->templatePrepend . '_' . $template;
        }, (array)$profile->Templates), (array)$profile->Templates) : $profile->Templates;
        $posts = $provider->all((array) $profile->getFeedSettings($this->parent));
        $postSettings = (array) $profile->getPostSettings($this->parent);

        foreach($posts as $post) {
	        if($this->isOverTheLimit())
		        break;

            if(!($post instanceof \ViewableData)) {
                $post = $profile->processPost(array_merge($postSettings, $post));

	            if(isset($post['hidden']) && $post['hidden'])
		            continue;

                $post['Profile'] = $profile;
                $this->convertToArrayData($post);
                $post['forTemplate'] = \ArrayData::create($post)->renderWith($template);
                $feed[] = \ArrayData::create($post);
            }
            else {
                $profile->processPost($postSettings, $post);

	            if(isset($post->hidden) && $post->hidden)
		            continue;

                foreach($postSettings as $setting => $value)
                    $post->$setting = $value;

                $post->Profile = $profile;
                $post->forTemplate = $post->renderWith($template);
                $feed[] = $post;
            }

	        $this->collected++;
        }

        return $feed;
    }

    public function prependTemplate($templatePrepend = '') {
        $this->templatePrepend = $templatePrepend;
        return $this;
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

	protected function isOverTheLimit() {
		return $this->limit !== null && $this->collected >= $this->limit;
	}
} 