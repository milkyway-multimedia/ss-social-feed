<?php
/**
 * Milkyway Multimedia
 * Db.php
 *
 * @package reggardocolaianni.com
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

                if(!$data->Posted)
                    $data->Posted = \DBField::create_field('Datetime', $child->obj('Created')->Value);

                if(!$data->Priority)
                    $data->Priority = strtotime($data->Posted);

                if(!$data->Link && $data->hasMethod('Link'))
                    $data->AuthorURL = $data->Link();

                $list[] = $data;
            }
        }

        return $list;
    }
} 