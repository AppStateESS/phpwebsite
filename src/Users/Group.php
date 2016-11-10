<?php

namespace \Canopy\Users;

class Group
{

    private $id;
    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getMembers()
    {
    }

    public function addMember($member)
    {
    }

    public function removeMember($member)
    {
    }

    public function setMembers(Array $members)
    {
    }

    public function dropAllMembers()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {

    }

    public function getName()
    {
        return $this->name;
    }
}
