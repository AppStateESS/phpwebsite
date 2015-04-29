<?php

namespace contact\Resource\ContactInfo;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PhysicalAddress extends \Data
{
    /**
     * Room nummber in building
     * @var \Variable\Integer
     */
    private $room_number;

    /**
     * Name of building
     * @var \Variable\TextOnly
     */
    private $building;

    /**
     * 
     * @var \Variable\TextOnly
     */
    private $street;

    /**
     * Post Office box
     * @var \Variable\Integer
     */
    private $post_box;

    /**
     * @var \Variable\TextOnly
     */
    private $city;

    /**
     * @var \Variable\TextOnly
     */
    private $state;

    /**
     * @var \Variable\Integer
     */
    private $zip;

    public function __construct()
    {
        $this->room_number = new \Variable\Integer(null, 'room_number');
        $this->building = new \Variable\TextOnly(null, 'building');
        $this->street = new \Variable\TextOnly(null, 'street');
        $this->post_box = new \Variable\Integer(null, 'post_box');
        $this->city = new \Variable\TextOnly(null, 'city');
        $this->state = new \Variable\TextOnly(null, 'state');
        $this->zip = new \Variable\Integer(null, 'zip');
    }

    public function getRoomNumber()
    {
        return $this->room_number->get();
    }

    public function getBuilding()
    {
        return $this->building->get();
    }

    public function getStreet()
    {
        return $this->street->get();
    }

    public function getPostBox()
    {
        return $this->post_box->get();
    }

    public function getCity()
    {
        return $this->city->get();
    }

    public function getState()
    {
        return $this->state->get();
    }

    public function getZip()
    {
        return $this->zip->get();
    }

    public function getValues()
    {
        $values['room_number'] = $this->getRoomNumber();
        $values['building'] = $this->getBuilding();
        $values['street'] = $this->getStreet();
        $values['post_box'] = $this->getPostBox();
        $values['city'] = $this->getCity();
        $values['state'] = $this->getState();
        $values['zip'] = $this->getZip();
        return $values;
    }

}
