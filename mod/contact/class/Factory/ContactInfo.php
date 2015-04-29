<?php

namespace contact\Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ContactInfo
{

    /**
     * 
     * @return \contact\Resource\ContactInfo
     */
    private static function getContactInfo()
    {
        $contact_info = new \contact\Resource\ContactInfo;
        return $contact_info;
    }

    public static function form()
    {
        $contact_info = self::getContactInfo();
        $values = self::extractValues($contact_info);
        $template = new \Template($values);
        $template->setModuleTemplate('contact', 'Contact_Info_Form.html');
        return $template->get();
    }

    private static function extractValues(\contact\Resource\ContactInfo $contact_info)
    {
        $physical_address = $contact_info->getPhysicalAddress();

        $values = $physical_address->getValues();

        $values['phone_number'] = $contact_info->getPhoneNumber();
        $values['fax_number'] = $contact_info->getFaxNumber();

        $offsite = $contact_info->getOffsite();

        $links = $offsite->getLinks();

        if (!empty($links)) {
            foreach ($links as $l) {
                $values['offline'][] = array('icon'=>$l->getIcon(), 'title'=>$l->getTitle(),
                    'url'=>$l->getUrl());
            }
        }

        $map = $contact_info->getMap();
        $values = array_merge($values, $map->getValues());
        return $values;
    }

}
