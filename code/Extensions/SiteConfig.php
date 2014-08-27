<?php namespace Milkyway\SS\SocialFeed\Extensions;

/**
 * Milkyway Multimedia
 * SiteConfig.php
 *
 * @package relatewell.org.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class SiteConfig extends \DataExtension
{
    private static $db = [
        'Facebook_Username'         => 'Varchar',
        'Facebook_ApplicationId'    => 'Varchar',
        'Twitter_Username'          => 'Varchar',
        'Twitter_ApiKey'            => 'Varchar',
        'Twitter_ApiSecret'         => 'Varchar',
        'Twitter_AccessToken'       => 'Varchar',
        'Twitter_AccessTokenSecret' => 'Varchar',
        'GooglePlus_Username'       => 'Varchar',
        'GooglePlus_ApiKey'         => 'Varchar',
        'Instagram_Username'        => 'Varchar(200)',
        'AddThis'                   => 'Varchar(32)',
    ];
}