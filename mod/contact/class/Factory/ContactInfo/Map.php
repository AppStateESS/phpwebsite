<?php

namespace contact\Factory\ContactInfo;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map
{

    public static function getGoogleSearchString(\contact\Resource\ContactInfo\PhysicalAddress $physical_address)
    {
        $building = $physical_address->getBuilding();
        $street = $physical_address->getStreet();
        $city = $physical_address->getCity();
        $state = $physical_address->getState();

        if (empty($building) || empty($street) || empty($city) || empty($state)) {
            throw new \Exception('Building, street, city and state are required information.');
        } else {
            $address = "$building,+$street,+$city,+$state";
            return $address;
        }
    }

    public static function getImageUrl($latitude, $longitude)
    {
        $map = self::load();
        $size = \Settings::get('contact', 'dimension_x') . 'x' . \Settings::get('contact', 'dimension_y');
        $zoom = \Settings::get('contact', 'zoom');
        return "https://maps.googleapis.com/maps/api/staticmap?center=$latitude,$longitude&size=$size&maptype=roadmap&zoom=$zoom&markers=color:red%7Clabel:A%7C$latitude,$longitude";
    }

    public static function getMapUrl($latitude, $longitude)
    {
        $map = self::load();
        $zoom = $map->getZoom();
        return "https://www.google.com/maps/place/$latitude,$longitude/z=$zoom";
    }

    public static function getValues(\contact\Resource\ContactInfo\Map $map)
    {
        $values['thumbnail_map'] = $map->getThumbnailMap();
        $values['latitude'] = $map->getLatitude();
        $values['longitude'] = $map->getLongitude();
        $values['full_map_link'] = $map->getFullMapLink();
        $values['zoom'] = $map->getZoom();
        $values['dimension_x'] = $map->getDimensionX();
        $values['dimension_y'] = $map->getDimensionY();
        return $values;
    }

    public static function load()
    {
        $map = new \contact\Resource\ContactInfo\Map;

        $map->setThumbnailMap(\Settings::get('contact', 'thumbnail_map'));
        $map->setLatitude(\Settings::get('contact', 'latitude'));
        $map->setLongitude(\Settings::get('contact', 'longitude'));
        $map->setFullMapLink(\Settings::get('contact', 'full_map_link'));
        $map->setZoom(\Settings::get('contact', 'zoom'));
        $map->setDimensionX(\Settings::get('contact', 'dimension_x'));
        $map->setDimensionY(\Settings::get('contact', 'dimension_y'));
        return $map;
    }

    public static function save(\contact\Resource\ContactInfo\Map $map)
    {
        $values = self::getValues($map);
        foreach ($values as $key => $val) {
            \Settings::set('contact', $key, $val);
        }
    }

    public static function createMapThumbnail($latitude, $longitude)
    {
        $google_url = self::getImageUrl($latitude, $longitude);
        $curl = \curl_init($google_url);

        $filename = 'images/contact/googlemap_' . time() . '.png';
        $fp = fopen(PHPWS_HOME_DIR . $filename, "w");
        \curl_setopt($curl, CURLOPT_FILE, $fp);
        \curl_setopt($curl, CURLOPT_HEADER, 0);

        \curl_exec($curl);
        \curl_close($curl);
        fclose($fp);

        $map = self::load();
        $map->setThumbnailMap($filename);
        $map->setLatitude($latitude);
        $map->setLongitude($longitude);
        $map->setFullMapLink(self::getMapUrl($latitude, $longitude));
        self::save($map);
    }

}
