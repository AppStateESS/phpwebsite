<?php

namespace contact\Resource\ContactInfo;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map extends \Data
{
    private $image;
    private $map_link;

    public function __construct()
    {
        $this->image = new \Variable\File(null, 'image');
        $this->map_link = new \Variable\Url(null, 'map_link');
    }

    public function getValues()
    {
        $values['image'] = $this->getImage();
        $values['map_link'] = $this->getMapLink();
        return $values;
    }

    public function getImage()
    {
        return $this->image->get();
    }
    
    public function getMapLink()
    {
        return $this->map_link->get();
    }

}
