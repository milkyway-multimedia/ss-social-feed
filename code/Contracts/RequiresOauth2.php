<?php namespace Milkyway\SS\SocialFeed\Contracts;

/**
 * Milkyway Multimedia
 * RequiresOauth2.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

interface RequiresOauth2 {
    public function extendedPermissions($params = []);
} 