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
            $admin = new \contact\Controller\Admin($this);
            return $admin;
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
        $settings['building'] = null;
        $settings['room_number'] = null;
        $settings['phone_number'] = null;
        $settings['fax_number'] = null;
        $settings['email'] = null;

        // Physical Address
        $settings['street'] = null;
        $settings['post_box'] = null;
        $settings['city'] = null;
        $settings['state'] = 'NC';
        $settings['zip'] = null;
        
        // Offsite
        $settings['links'] = null;

        // Map
        $settings['thumbnail_map'] = null;
        $settings['latitude'] = null;
        $settings['longitude'] = null;
        $settings['full_map_link'] = null;
        
        $settings['zoom'] = 17;
        $settings['dimension_x'] = '300';
        $settings['dimension_y'] = '300';

        return $settings;
    }

}

?>
