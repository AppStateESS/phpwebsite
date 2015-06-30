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
        $this->room_number->allowNull(true);
        $this->building = new \Variable\TextOnly(null, 'building');
        $this->building->allowNull(true);
        $this->street = new \Variable\TextOnly(null, 'street');
        $this->street->allowNull(true);
        $this->post_box = new \Variable\Integer(null, 'post_box');
        $this->post_box->allowNull(true);
        $this->city = new \Variable\TextOnly(null, 'city');
        $this->city->allowNull(true);
        $this->state = new \Variable\TextOnly(null, 'state');
        $this->state->allowNull(true);
        $this->zip = new \Variable\String(null, 'zip');
        $this->zip->allowNull(true);
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

    public function setBuilding($building)
    {
        $this->building->set($building);
    }

    public function setRoomNumber($room_number)
    {
        if (empty($room_number)) {
            $this->room_number->set(null);
        } else {
            $this->room_number->set($room_number);
        }
    }

    public function setPostBox($post_box)
    {
        if (empty($post_box)) {
            $post_box = null;
        }
        $this->post_box->set($post_box);
    }

    public function setStreet($street)
    {
        $this->street->set($street);
    }

    public function setCity($city)
    {
        $this->city->set($city);
    }

    public function setState($state)
    {
        $this->state->set($state);
    }

    public function setZip($zip)
    {
        $this->zip->set($zip);
    }

}
