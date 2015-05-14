<?php

namespace contact\Factory;

use contact\Resource\ContactInfo\PhysicalAddress;
use contact\Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ContactInfo
{

    public static function form(\Request $request, $active_tab)
    {
        javascript('jquery');
        \Form::requiredScript();

        if (!in_array($active_tab, array('contact-info', 'map', 'social'))) {
            $active_tab = 'contact-info';
        }

        $thumbnail_map = \Settings::get('contact', 'thumbnail_map');

        $contact_info = self::load();
        $values = self::getValues($contact_info);
        require PHPWS_SOURCE_DIR . 'mod/contact/config/states.php';
        $values['states'] = & $states;
        if (!empty($thumbnail_map)) {
            $values['thumbnail_map'] = "<img src='$thumbnail_map' />";
        } else {
            $values['thumbnail_map'] = null;
        }
        
        $js_social_links = ContactInfo\Social::getLinksAsJavascriptObject($values['social_links']);
        
        $js_string = <<<EOF
<script type='text/javascript'>var active_tab = '$active_tab';var thumbnail_map = '$thumbnail_map';var social_urls = $js_social_links;</script>
EOF;

        \Layout::addJSHeader($js_string);
        $script = PHPWS_SOURCE_HTTP . 'mod/contact/javascript/contact.js';
        \Layout::addJSHeader("<script type='text/javascript' src='$script'></script>");
        \Layout::addJSHeader('<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>');
        
        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'Contact_Info_Form.html');
        return $template->get();
    }

    public static function load()
    {
        $contact_info = new \contact\Resource\ContactInfo;
        $contact_info->setPhoneNumber(\Settings::get('contact', 'phone_number'));
        $contact_info->setFaxNumber(\Settings::get('contact', 'fax_number'));
        $contact_info->setEmail(\Settings::get('contact', 'email'));

        $contact_info->setPhysicalAddress(ContactInfo\PhysicalAddress::load());
        $contact_info->setMap(Factory\ContactInfo\Map::load());
        $contact_info->setSocial(Factory\ContactInfo\Social::load());
        return $contact_info;
    }

    private static function getValues(\contact\Resource\ContactInfo $contact_info)
    {
        $values['phone_number'] = $contact_info->getPhoneNumber();
        $values['fax_number'] = $contact_info->getFaxNumber();
        $values['email'] = $contact_info->getEmail();
        $values['formatted_phone_number'] = $contact_info->getPhoneNumber(true);
        $values['formatted_fax_number'] = $contact_info->getFaxNumber(true);

        $physical_address = $contact_info->getPhysicalAddress();
        $map = $contact_info->getMap();
        $social = $contact_info->getSocial();

        $values = array_merge($values, ContactInfo\PhysicalAddress::getValues($physical_address));
        $values = array_merge($values, ContactInfo\Map::getValues($map));
        $values = array_merge($values, ContactInfo\Social::getValues($social));

        return $values;
    }

    public static function post(\contact\Resource\ContactInfo $contact_info, $values)
    {
        $contact_info->setPhoneNumber($values['phone_number']);
        $contact_info->setFaxNumber($values['fax_number']);
        $contact_info->setEmail($values['email']);
        self::save($contact_info);

        $physical_address = $contact_info->getPhysicalAddress();
        Factory\ContactInfo\PhysicalAddress::set($physical_address, $values);
        Factory\ContactInfo\PhysicalAddress::save($physical_address);
    }

    private static function save(\contact\Resource\ContactInfo $contact_info)
    {
        \Settings::set('contact', 'phone_number', $contact_info->getPhoneNumber());
        \Settings::set('contact', 'fax_number', $contact_info->getFaxNumber());
        \Settings::set('contact', 'email', $contact_info->getEmail());
    }

    public static function display()
    {
        // no city data (required) no output
        if (empty(\Settings::get('contact', 'city'))) {
            return;
        }
        $contact_info = self::load();
        $values = self::getValues($contact_info);

        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'view.html');
        $content = $template->get();
        return $content;
    }

}
