<?php

namespace Canopy\Users;

class User {

    private $id;

    private $username;
    private $email;
    private $fullName;

    private $isDeity;

    private $authenticationMethodName;
    private $authorizationMethodName;

    private $lastLoginTime;
    private $loginCount;

    private $createdOnTime;
    private $lastModifiedTime;


    public function __construct($username, $email, $fullName, AuthenticationMethod $authenticationMethod, AuthorizationMethod $authorizationMethod, $isDeity) {
        $this->id = null;
        $this->username = $username;
        $this->email = $email;
        $this->fullName = $fullName;

        $this->authenticationMethodName = $authenticationMethod->getName();
        $this->authorizationMethodName = $authorizationMethod->getName();
        $this->isDeity = $isDeity;

        $this->lastLoginTime = null;
        $this->loginCount = 0;
        $this->createdOnTime = time();
        $this->lastModifiedTime = time();
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

    public function getCreatedOnTime(){
        return $this->createdOnTime;
    }

    public function getLastModifiedTime(){
        return $this->lastModifiedTime;
    }

    public function getAuthenticationMethodName(){
        return $this->authenticationMethodName;
    }

    public function getAuthorizationMethodName(){
        return $this->authorizationMethodName;
    }
}
