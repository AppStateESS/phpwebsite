<?php

namespace contact\Factory;

use contact\Resource\ContactInfo\PhysicalAddress;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ContactInfo
{

    public static function fetchContactInfo()
    {
        $contact_info = new \contact\Resource\ContactInfo;
        self::loadContactInfo($contact_info);
        return $contact_info;
    }

    public static function form(\Request $request)
    {
        javascript('jquery');
        \Form::requiredScript();
        $script = PHPWS_SOURCE_HTTP . 'mod/contact/javascript/contact.js';
        \Layout::addJSHeader('<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>');
        \Layout::addJSHeader("<script type='text/javascript' src='$script'></script>");

        
        $contact_info = self::fetchContactInfo();
        $values = self::extractValues($contact_info);
        require PHPWS_SOURCE_DIR . 'mod/contact/config/states.php';
        $values['states'] = & $states;
        
        $active_tab = $request->shiftCommand();
        if (!in_array($active_tab, array('contact-info', 'map', 'social'))) {
            $active_tab = 'contact-info';
        }
        
        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'Contact_Info_Form.html');
        return $template->get();
    }

    private static function loadContactInfo(\contact\Resource\ContactInfo $contact_info)
    {
        $contact_info->setPhoneNumber(\Settings::get('contact', 'phone_number'));
        $contact_info->setFaxNumber(\Settings::get('contact', 'fax_number'));
        $contact_info->setEmail(\Settings::get('contact', 'email'));

        $physical_address = $contact_info->getPhysicalAddress();
        self::loadPhysicalAddress($physical_address);
    }

    private static function loadPhysicalAddress(PhysicalAddress $physical_address)
    {
        $physical_address->setRoomNumber(\Settings::get('contact', 'room_number'));
        $physical_address->setBuilding(\Settings::get('contact', 'building'));
        $physical_address->setStreet(\Settings::get('contact', 'street'));
        $physical_address->setPostBox(\Settings::get('contact', 'post_box'));
        $physical_address->setCity(\Settings::get('contact', 'city'));
        $physical_address->setState(\Settings::get('contact', 'state'));
        $physical_address->setZip(\Settings::get('contact', 'zip'));
    }

    private static function extractValues(\contact\Resource\ContactInfo $contact_info)
    {
        $physical_address = $contact_info->getPhysicalAddress();

        $values = $physical_address->getValues();

        $values['phone_number'] = $contact_info->getPhoneNumber();
        $values['fax_number'] = $contact_info->getFaxNumber();
        $values['email'] = $contact_info->getEmail();
        $values['formatted_phone_number'] = $contact_info->getPhoneNumber(true);
        $values['formatted_fax_number'] = $contact_info->getFaxNumber(true);

        $offsite = $contact_info->getOffsite();

        $links = $offsite->getLinks();

        if (!empty($links)) {
            foreach ($links as $l) {
                $values['offline'][] = array('icon' => $l->getIcon(), 'title' => $l->getTitle(),
                    'url' => $l->getUrl());
            }
        }

        $map = $contact_info->getMap();
        $values = array_merge($values, $map->getValues());
        return $values;
    }

    public static function postContactInfo(\contact\Resource\ContactInfo $contact_info, $values)
    {
        $contact_info->setPhoneNumber($values['phone_number']);
        $contact_info->setFaxNumber($values['fax_number']);
        $contact_info->setEmail($values['email']);
        self::saveContactInfo($contact_info);

        $physical_address = $contact_info->getPhysicalAddress();
        self::postPhysicalAddress($physical_address, $values);
        self::savePhysicalAddress($physical_address);
    }

    private static function saveContactInfo(\contact\Resource\ContactInfo $contact_info)
    {
        \Settings::set('contact', 'phone_number', $contact_info->getPhoneNumber());
        \Settings::set('contact', 'fax_number', $contact_info->getFaxNumber());
        \Settings::set('contact', 'email', $contact_info->getEmail());
    }

    private static function postPhysicalAddress(PhysicalAddress $physical_address, $values)
    {
        $physical_address->setBuilding($values['building']);
        $physical_address->setRoomNumber($values['room_number']);
        $physical_address->setPostBox($values['post_box']);
        $physical_address->setStreet($values['street']);
        $physical_address->setCity($values['city']);
        $physical_address->setState($values['state']);
        $physical_address->setZip($values['zip']);
    }

    private static function savePhysicalAddress(PhysicalAddress $physical_address)
    {
        \Settings::set('contact', 'building', $physical_address->getBuilding());
        \Settings::set('contact', 'room_number', $physical_address->getRoomNumber());
        \Settings::set('contact', 'post_box', $physical_address->getPostBox());
        \Settings::set('contact', 'street', $physical_address->getStreet());
        \Settings::set('contact', 'city', $physical_address->getCity());
        \Settings::set('contact', 'state', $physical_address->getState());
        \Settings::set('contact', 'zip', $physical_address->getZip());
    }

    public static function display()
    {
        $contact_info = self::fetchContactInfo();
        $values = self::extractValues($contact_info, true);

        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'view.html');
        $content = $template->get();
        return $content;
    }

}
