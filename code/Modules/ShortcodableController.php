<?php namespace Milkyway\SS\SocialFeed\Modules;

/**
 * Milkyway Multimedia
 * Shortcodable.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class ShortcodableController extends \Extension
{
    public function updateShortcodeForm($form)
    {
        singleton('ShortcodableParser')->register('google_plus_follow');
        singleton('ShortcodableParser')->register('google_plus_one');

        singleton('ShortcodableParser')->register('twitter_follow');
        singleton('ShortcodableParser')->register('twitter_mention');

        singleton('ShortcodableParser')->register('facebook_like');

        singleton('ShortcodableParser')->register('addthis');

        $classname     = false;
        $shortcodeData = false;

        if ($shortcode = $this->owner->Request->requestVar('Shortcode')) {
            $shortcodeData = singleton('ShortcodableParser')->the_shortcodes([], $shortcode);
            if (isset($shortcodeData[0])) {
                $shortcodeData = $shortcodeData[0];
                $classname     = $shortcodeData['name'];
            }
        } else {
            $classname = $this->owner->Request->requestVar('ShortcodeType');
        }

        if ($types = $form->Fields()->fieldByName('ShortcodeType')) {
            $source = array_merge(
                $types->Source,
                [
                    'addthis'        => _t('Shortcodable.ADDTHIS', 'Share This Page Button Set'),
                    'facebook_like'  => _t('Shortcodable.FACEBOOK_LIKE', 'Facebook Like Button'),
                    'twitter_follow' => _t('Shortcodable.TWITTER_FOLLOW', 'Twitter Follow Button'),
                    'twitter_mention' => _t('Shortcodable.TWITTER_MENTION', 'Twitter Mention Button'),
                    'google_plus_follow' => _t('Shortcodable.GOOGLE_PLUS_FOLLOW', 'Follow on Google+ Button'),
                    'google_plus_one' => _t('Shortcodable.GOOGLE_PLUS_ONE', 'Google Plus One Button'),
                ]
            );

            $types->setSource($source);

            if ($classname) {
                $types->setValue($classname);
            }

            if ($types->Value()) {
                switch ($types->Value()) {
                    case 'addthis':
                        $form->Fields()->push(
                            \CompositeField::create(
                                \FieldList::create(
                                    \TextField::create(
                                        'link',
                                        _t('Shortcodable.LINK', 'Link')
                                    )->setAttribute('placeholder', _t('Shortcodable.DEFAULT-ADDTHIS-LINK', 'Current Page URL'))->setForm($form),
                                    \TextField::create(
                                        'title',
                                        _t('Shortcodable.TITLE', 'Title')
                                    )->setAttribute('placeholder', _t('Shortcodable.DEFAULT-ADDTHIS-TITLE', 'Current Page Title'))->setForm($form),
                                    \DropdownField::create(
                                        'counter',
                                        _t('Shortcodable.ADDTHIS-COUNTER', 'Display counter'),
                                        [
                                            ''  => 'No',
                                            '1' => 'Yes',
                                        ]
                                    )->setForm($form),
                                    \TextField::create(
                                        'user',
                                        _t('Shortcodable.ADDTHIS-PROFILEID', 'Profile ID')
                                    )
                                        ->setDescription('AddThis Profile ID used throughout the website for sharing etc. (format: <strong>ra-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</strong>)')
                                        ->setAttribute('placeholder', singleton('SocialFeed_Profile')->getValueFromEnvironment('AddThis'))
                                        ->setForm($form)
                                )
                            )->addExtraClass('attributes-composite')
                        );
                        break;
                    case 'facebook_like':
                        $form->Fields()->push(
                            \CompositeField::create(
                                \FieldList::create(
                                    \TextField::create(
                                        'link',
                                        _t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
                                    )->setAttribute('placeholder', singleton('SocialFeed_Facebook')->getValueFromEnvironment('Username'))->setForm($form),
                                    \DropdownField::create(
                                        'action',
                                        _t('Shortcodable.FB-ACTION', 'Action'),
                                        [
                                            ''  => '(default)',
                                            'like' => 'Like',
                                            'recommend' => 'Recommend',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'scheme',
                                        _t('Shortcodable.FB-SCHEME', 'Scheme'),
                                        [
                                            ''  => '(default)',
                                            'light' => 'light',
                                            'dark' => 'dark',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'layout',
                                        _t('Shortcodable.FB-LAYOUT', 'Layout'),
                                        [
                                            ''  => '(default)',
                                            'standard' => 'Standard',
                                            'button_count' => 'Button Count',
                                            'box_count' => 'Box Count',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'share',
                                        _t('Shortcodable.FB-SHARE', 'Allow Sharing'),
                                        [
                                            ''  => 'No',
                                            '1' => 'Yes',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'show_faces',
                                        _t('Shortcodable.FB-SHOW_FACES', 'Show profile photos'),
                                        [
                                            ''  => 'No',
                                            '1' => 'Yes',
                                        ]
                                    )->setForm($form),
                                    \TextField::create(
                                        'ref',
                                        _t('Shortcodable.FB-REFERRAL_REFERENCE', 'Reference for referrals')
                                    )
                                        ->setDescription('A label for tracking referrals which must be less than 50 characters')
                                        ->setMaxLength(50)
                                        ->setForm($form)
                                )
                            )->addExtraClass('attributes-composite')
                        );
                        break;
                    case 'twitter_follow':
                    case 'twitter_mention':
                        $form->Fields()->push(
                            \CompositeField::create(
                                \FieldList::create(
                                    \TextField::create(
                                        'link',
                                        _t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
                                    )->setAttribute('placeholder', singleton('SocialFeed_Twitter')->getValueFromEnvironment('Username'))->setForm($form),
                                    \DropdownField::create(
                                        'share',
                                        _t('Shortcodable.TWITTER-COUNT', 'Show count'),
                                        [
                                            '1'  => 'Yes',
                                            '' => 'No',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'show_faces',
                                        _t('Shortcodable.FB-SHOW_SCREEN_NAME', 'Show screen name'),
                                        [
                                            ''  => 'No',
                                            '1' => 'Yes',
                                        ]
                                    )->setForm($form)
                                )
                            )->addExtraClass('attributes-composite')
                        );
                        break;
                    case 'google_plus_follow':
                    case 'google_plus_one':
                        $form->Fields()->push(
                            \CompositeField::create(
                                \FieldList::create(
                                    \TextField::create(
                                        'link',
                                        _t('Shortcodable.LINK_OR_USERNAME', 'Link/Username')
                                    )->setAttribute('placeholder', singleton('SocialFeed_Twitter')->getValueFromEnvironment('Username'))->setForm($form),
                                    \DropdownField::create(
                                        'annotation',
                                        _t('Shortcodable.G+-ANNOTATIONS', 'Display annotation'),
                                        [
                                            'bubble'  => 'Bubble next to button with counter',
                                            'inline' => 'Inline with avatars of users who have followed page',
                                            '' => 'None',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'size',
                                        _t('Shortcodable.G+-SIZE', 'Button size'),
                                        [
                                            ''  => '(default)',
                                            'standard' => 'Standard',
                                            'small' => 'Small',
                                            'medium' => 'Medium',
                                            'tall' => 'Tall',
                                        ]
                                    )->setForm($form),
                                    \DropdownField::create(
                                        'share',
                                        _t('Shortcodable.G+-RECOMMENDATIONS', 'Display Recommendations'),
                                        [
                                            ''  => 'No',
                                            '1' => 'Yes',
                                        ]
                                    )->setForm($form)
                                )
                            )->addExtraClass('attributes-composite')
                        );
                        break;
                }
            }
        }

        if ($shortcodeData && isset($shortcodeData['atts'])) {
            $form->loadDataFrom($shortcodeData['atts']);
        }
    }
}