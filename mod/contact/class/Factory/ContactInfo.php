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
        javascript('ckeditor');
        \Form::requiredScript();

        if (!in_array($active_tab, array('contact-info', 'map', 'social'))) {
            $active_tab = 'contact-info';
        }

        $thumbnail_map = \Settings::get('contact', 'thumbnail_map');

        $contact_info = self::load();
        $values = self::getValues($contact_info);
        require PHPWS_SOURCE_DIR . 'mod/contact/config/states.php';
        $values['states'] = $states;
        if (!empty($thumbnail_map)) {
            $values['thumbnail_map'] = "<img src='$thumbnail_map' />";
        } else {
            $values['thumbnail_map'] = null;
        }
        $js_social_links = ContactInfo\Social::getLinksAsJavascriptObject($values['social']);

        $js_string = <<<EOF
<script type='text/javascript'>var active_tab = '$active_tab';var thumbnail_map = '$thumbnail_map';var social_urls = $js_social_links;</script>
EOF;

        if (isset($_SESSION['Contact_Message'])) {
            $values['message'] = $_SESSION['Contact_Message'];
            unset($_SESSION['Contact_Message']);
        }

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
        $contact_info->setSiteContactName(\Settings::get('contact', 'site_contact_name'));
        $contact_info->setSiteContactEmail(\Settings::get('contact', 'site_contact_email'));
        $contact_info->setOtherInformation(\Settings::get('contact', 'other_information'));

        $contact_info->setPhysicalAddress(ContactInfo\PhysicalAddress::load());
        $contact_info->setMap(Factory\ContactInfo\Map::load());
        $contact_info->setSocial(Factory\ContactInfo\Social::load());
        return $contact_info;
    }

    private static function getValues(\contact\Resource\ContactInfo $contact_info, $sort_social = false)
    {
        $values['phone_number'] = $contact_info->getPhoneNumber();
        $values['fax_number'] = $contact_info->getFaxNumber();
        $values['email'] = $contact_info->getEmail();
        $values['formatted_phone_number'] = $contact_info->getPhoneNumber(true);
        if ($values['fax_number']) {
            $values['formatted_fax_number'] = $contact_info->getFaxNumber(true);
        }

        $values['site_contact_name'] = $contact_info->getSiteContactName();
        $values['site_contact_email'] = $contact_info->getSiteContactEmail();
        $values['other_information'] = $contact_info->getOtherInformation();

        $physical_address = $contact_info->getPhysicalAddress();
        $map = $contact_info->getMap();

        $values = array_merge($values, ContactInfo\PhysicalAddress::getValues($physical_address));
        $values = array_merge($values, ContactInfo\Map::getValues($map));

        if ($sort_social) {
            $social = ContactInfo\Social::getLinks();
            foreach ($social as $label => $link) {
                if (isset($link['url'])) {
                    $social_icons[$label] = $link;
                }
            }
            if (!empty($social_icons)) {
                $values = array_merge($values, array('social' => $social_icons));
            }
        } else {
            $values = array_merge($values, array('social' => ContactInfo\Social::getLinks()));
        }
        return $values;
    }

    public static function post(\contact\Resource\ContactInfo $contact_info, $values)
    {
        $contact_info->setPhoneNumber($values['phone_number']);
        $contact_info->setFaxNumber($values['fax_number']);
        $contact_info->setEmail($values['email']);
        $contact_info->setSiteContactName($values['site_contact_name']);
        $contact_info->setSiteContactEmail($values['site_contact_email']);
        $contact_info->setOtherInformation($values['other_information']);
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
        \Settings::set('contact', 'site_contact_name', $contact_info->getSiteContactName());
        \Settings::set('contact', 'site_contact_email', $contact_info->getSiteContactEmail());
        \Settings::set('contact', 'other_information', $contact_info->getOtherInformation());
    }

    public static function showSiteContact()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/contact/config/default_message.php';
        $name = SITE_CONTACT_NAME;
        $email = SITE_CONTACT_EMAIL;
        $sc_name = \phpws2\Settings::get('contact', 'site_contact_name');
        $sc_email = \phpws2\Settings::get('contact', 'site_contact_email');
        if (!empty($sc_name) && !empty($sc_email)) {
            $name = $sc_name;
            $email = $sc_email;
        }
        if (empty($name) || empty($email)) {
            return;
        }
        $content = "Please report problems with this site or content errors to <a href='mailto:$email'>$name <i class='fa fa-envelope-o'></i></a>.";
        \Layout::add($content, 'contact', 'SITE_CONTACT');
    }

    public static function display()
    {
        $building = \Settings::get('contact', 'building');

        if (empty($building)) {
            return;
        }

        $contact_info = self::load();
        $values = self::getValues($contact_info, true);

        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'view.html');
        $content = $template->get();
        return $content;
    }

}
