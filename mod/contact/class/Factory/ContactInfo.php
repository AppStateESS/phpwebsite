<?php

namespace contact\Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ContactInfo
{
    public static function loadContactInfo()
    {
        $contact_info = new \contact\Resource\ContactInfo;
        return $contact_info;
    }
}
