<?php
/**
 * Milkyway Multimedia
 * Db.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\SocialFeed\Providers\Model;

use Milkyway\SS\SocialFeed\Contracts\Provider;

abstract class Internal implements Provider {
    protected $method = 'Children';

    public function __construct() {}

    protected function listFromMethod(\DataObject $object, $limit = 5) {
        $list = [];

        if($object->AllChildrenIncludingDeleted()->limit($limit)->exists()) {
            foreach($object->AllChildrenIncludingDeleted()->limit($limit) as $child) {
                $data = ($child instanceof \RedirectorPage) ? $child->ContentSource() : $child;
                $list[] = $this->process($data);
            }
        }

        return $list;
    }

    protected function process($item) {
        if(!$item->Posted)
            $item->Posted = \DBField::create_field('Datetime', $item->obj('Created')->Value);

        if(!$item->Priority)
            $item->Priority = strtotime($item->Posted);

        if(!$item->Link && $item->hasMethod('Link'))
            $item->AuthorURL = $item->Link();

        return $item;
    }
} 