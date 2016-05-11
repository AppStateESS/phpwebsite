<?php

namespace contact\Resource\ContactInfo;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map extends \Data
{
    private $thumbnail_map;
    private $latitude;
    private $longitude;
    private $full_map_link;
    private $zoom;
    private $dimension_x;
    private $dimension_y;

    public function __construct()
    {
        $this->thumbnail_map = new \Variable\File(null, 'thumbnail_map');
        $this->thumbnail_map->allowNull(true);
        $this->thumbnail_map->allowEmpty(true);
        $this->latitude = new \Variable\Float(null, 'latitude');
        $this->longitude = new \Variable\Float(null, 'longitude');
        $this->full_map_link = new \Variable\Url(null, 'full_map_link');
        $this->full_map_link->allowNull(true);
        $this->zoom = new \Variable\Integer(null, 'zoom');
        $this->dimension_x = new \Variable\Integer(null, 'dimension_x');
        $this->dimension_y = new \Variable\Integer(null, 'dimension_y');
    }

    public function setThumbnailMap($thumbnail_map)
    {
        $this->thumbnail_map->set($thumbnail_map);
    }

    public function setLatitude($latitude)
    {
        $latitude = (float)$latitude;
        $this->latitude->set($latitude);
    }

    public function setLongitude($longitude)
    {
        $longitude = (float)$longitude;
        $this->longitude->set($longitude);
    }

    public function setFullMapLink($full_map_link)
    {
        $this->full_map_link->set($full_map_link);
    }

    public function setZoom($zoom)
    {
        $this->zoom->set($zoom);
    }

    public function setDimensionX($dim_x)
    {
        $this->dimension_x->set($dim_x);
    }

    public function setDimensionY($dim_y)
    {
        $this->dimension_y->set($dim_y);
    }

     public function getThumbnailMap()
    {
        return $this->thumbnail_map->get();
    }

    public function getLatitude()
    {
        return $this->latitude->get();
    }

    public function getLongitude()
    {
        return $this->longitude->get();
    }

    public function getFullMapLink()
    {
        return $this->full_map_link->get();
    }

    public function getZoom()
    {
        return $this->zoom->get();
    }

    public function getDimensionX()
    {
        return $this->dimension_x->get();
    }

    public function getDimensionY()
    {
        return $this->dimension_y->get();
    }

}
