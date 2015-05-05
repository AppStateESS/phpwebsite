<?php

namespace contact\Factory\ContactInfo;

use contact\Resource\ContactInfo\PhysicalAddress;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map
{

    public static function getGoogleSearchString(PhysicalAddress $physical_address)
    {
        $building = $physical_address->getBuilding();
        $street = $physical_address->getStreet();
        $city = $physical_address->getCity();
        $state = $physical_address->getState();

        if (empty($building) || empty($street) || empty($city) || empty($state)) {
            return array('error' => 'Building, street, city and state are required information.');
        } else {
            $address = "$building,+$street,+$city,+$state";
            return array('address' => $address);
        }
    }

    public static function getImageUrl($lat_long)
    {
        return "https://maps.googleapis.com/maps/api/staticmap?center=$lat_long&size=300x300&maptype=roadmap&zoom=17&markers=color:red%7Clabel:A%7C$lat_long";
    }
    
    public static function getMapUrl($lat_long)
    {
        return "https://www.google.com/maps/place/$lat_long/z=17";
    }

}
