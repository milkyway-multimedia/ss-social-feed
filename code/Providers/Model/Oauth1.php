<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

/**
 * Milkyway Multimedia
 * Oauth1.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\SocialFeed\Contracts\RequiresOauth1;

abstract class Oauth1 extends HTTP implements RequiresOauth1 {
	public function getOauth1Configuration() {
		return $this->configuration;
	}
} 