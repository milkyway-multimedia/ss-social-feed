<?php namespace Milkyway\SS\SocialFeed\Cache;

/**
 * Milkyway Multimedia
 * Silverstripe.php
 *
 * @package milkyway-multimedia/milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;
use Exception;
use SS_Cache;

class Silverstripe implements CacheStorageInterface, \Flushable
{
    protected static $_cache;

    protected $cacheLifetime;

    public static function cache()
    {
        if (!static::$_cache) {
            static::$_cache = SS_Cache::factory(
                singleton('mwm')->clean_cache_key(__CLASS__),
                'Output',
                [
                    'lifetime' => singleton('env')->get('SocialFeed.cache_lifetime', 6) * 60 * 60,
                ]
            );
        }

        return static::$_cache;
    }

    public static function flush()
    {
        static::cache()->clean();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        $entry = null;

        try {
            return unserialize($this->cache()->load($key));
        } catch (Exception $ignored) {
            return $entry;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, CacheEntry $data)
    {
        try {
            $lifeTime = $data->getTTL();

            if ($lifeTime >= 0) {
                return $this->cache()->save(
                    $key,
                    serialize($data),
                    [],
                    $lifeTime
                );
            }
        } catch (Exception $ignored) {
            // No fail if we can't save it the storage
        }
        return false;
    }
}