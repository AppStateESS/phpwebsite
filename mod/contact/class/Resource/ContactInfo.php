<?php
namespace contact\Resource;
use contact\Resource\ContactInfo;

class ContactInfo {
    private $physical_address;
    private $phone_number;
    private $fax_number;
    private $offsite;
    private $map;
    
    public function __construct()
    {
        $this->physical_address = new ContactInfo\PhysicalAddress;
    }
}
