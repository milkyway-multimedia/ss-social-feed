<?php
/**
 * Milkyway Multimedia
 * SS_Blog.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Providers;

use Milkyway\SS\SocialFeed\Providers\Model\Internal;

class SS_Page extends Internal {
    public function all($settings = [])
    {
        $all = [];

        if(!isset($settings['page']))
            return $all;

        try {
            $all = $this->listFromMethod($settings['page'], isset($settings['limit']) ? $settings['limit'] : 5);
        } catch (\Exception $e) {
            \Debug::show($e->getMessage());
        }

        return $all;
    }
}