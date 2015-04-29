<?php
namespace contact\Resource;
use contact\Resource\ContactInfo;

class ContactInfo extends \Resource {
    private $physical_address;
    private $phone_number;
    private $fax_number;
    private $offsite;
    private $map;
    
    public function __construct()
    {
        $this->physical_address = new ContactInfo\PhysicalAddress;
        $this->phone_number = new \Variable\String(null, 'phone_number');
        $this->fax_number = new \Variable\String(null, 'phone_number');
        $this->fax_number->allowEmpty(true);
        $this->offsite = new ContactInfo\Offsite;
        $this->map = new ContactInfo\Map;
    }
    
    /**
     * 
     * @return contact\Resource\ContactInfo\PhysicalAddress
     */
    public function getPhysicalAddress()
    {
        return $this->physical_address;
    }
    /**
     * 
     * @return contact\Resource\ContactInfo\Offsite
     */
    public function getOffsite()
    {
        return $this->offsite;
    }
    /**
     * 
     * @return contact\Resource\ContactInfo\Map
     */
    public function getMap()
    {
        return $this->map;
    }
    
    public function getPhoneNumber()
    {
        return $this->phone_number->get();
    }
    
    public function setPhoneNumber($phone)
    {
        $this->phone_number->set($phone);
    }

    public function getFaxNumber()
    {
        return $this->fax_number->get();
    }
    
    public function setFaxNumber($fax)
    {
        $this->fax_number->set($fax);
    }
}
