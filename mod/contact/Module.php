<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

namespace contact;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \Module implements \SettingDefaults
{

    public function __construct()
    {
        parent::__construct();
        $this->setTitle('contact');
        $this->setProperName('Contact');
    }

    public function getController(\Request $request)
    {
        $cmd = $request->shiftCommand();
        if ($cmd == 'admin' && \Current_User::allow('contact')) {
            $controller = $request->shiftCommand();

            switch ($controller) {
                case 'map':
                    $map = new \contact\Controller\Map($this);
                    return $map;

                case 'social':
                    $social = new \contact\Controller\Social($this);
                    return $social;

                case 'contactinfo':
                default:
                    $contact_info = new \contact\Controller\ContactInfo($this);
                    return $contact_info;
            }
        } else {
            \Current_User::requireLogin();
        }
    }

    public function runTime(\Request $request)
    {
        $content = Factory\ContactInfo::display();
        if (!empty($content)) {
            \Layout::add($content, 'contact', 'box');
        }
    }

    public function getSettingDefaults()
    {
        // ContactInfo
        $settings['building'] = '';
        $settings['room_number'] = '';
        $settings['phone_number'] = '';
        $settings['fax_number'] = '';
        $settings['email'] = '';
        $settings['site_contact_name'] = '';
        $settings['site_contact_email'] = '';

        // Physical Address
        $settings['street'] = '';
        $settings['post_box'] = '';
        $settings['city'] = '';
        $settings['state'] = 'NC';
        $settings['zip'] = '';

        // Offsite
        $settings['social'] = '';

        // Map
        $settings['thumbnail_map'] = '';
        $settings['latitude'] = '';
        $settings['longitude'] = '';
        $settings['full_map_link'] = '';

        $settings['zoom'] = 17;
        $settings['dimension_x'] = '300';
        $settings['dimension_y'] = '300';

        return $settings;
    }

}
