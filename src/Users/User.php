<?php

namespace Canopy\Users;

class User {

    private $id;

    private $username;
    private $email;
    private $fullName;

    private $isDeity;

    private $authenticationMethod;
    private $authorizationMethod;

    private $lastLoginTime;
    private $loginCount;

    private $createdOn;
    private $modifiedOn;


    public function __construct($username, $email, $fullName, $authenticationMethod, $authorizationMethod, $isDeity) {
        $this->id = null;
        $this->username = $username;
        $this->email = $email;
        $this->fullName = $fullName;
        $this->$authenticationMethod = $authenticationMethod;
        $this->authorizationMethod = $authorizationMethod;
        $this->isDeity = $isDeity;
    }

    public function getId(){
        return $this->id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getFullName(){
        return $this->fullName;
    }

    public function isDeity(){
        return $this->isDeity;
    }

    public function getLoginCount(){
        return $this->loginCount;
    }

    public function getLastLoginTime(){
        return $this->lastLoginTime;
    }

    public function getCreatedOn(){
        return $this->createdOn;
    }

    public function getModifiedOn(){
        return $this->modifiedOn;
    }
}
