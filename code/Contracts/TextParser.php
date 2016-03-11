<?php
/**
 * Milkyway Multimedia
 * PostParser.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Contracts;


interface TextParser {
    public function text($text);
}