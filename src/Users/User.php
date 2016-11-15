<?php

namespace Canopy\Users;

class User {

    protected $id;

    protected $username;
    protected $email;
    protected $full_name;

    protected $is_deity;

    protected $authentication_method_name;
    protected $authorization_method_name;

    protected $lastLogin_time;
    protected $login_count;

    protected $created_on_time;
    protected $last_modified_time;


    public function __construct($username, $email, $fullName, AuthenticationMethod $authenticationMethod, AuthorizationMethod $authorizationMethod, $isDeity) {
        $this->id = null;
        $this->username = $username;
        $this->email = $email;
        $this->full_name = $fullName;

        $this->authentication_method_name = $authenticationMethod->getName();
        $this->authorization_method_name = $authorizationMethod->getName();
        $this->is_deity = $isDeity;

        $this->last_login_time = null;
        $this->login_count = 0;
        $this->created_on_time = time();
        $this->last_modified_time = time();
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getFullName(){
        return $this->full_name;
    }

    public function isDeity(){
        return $this->is_deity;
    }

    public function getLoginCount(){
        return $this->login_count;
    }

    public function getLastLoginTime(){
        return $this->last_login_time;
    }

    public function getCreatedOnTime(){
        return $this->created_on_time;
    }

    public function getLastModifiedTime(){
        return $this->last_modified_time;
    }

    public function getAuthenticationMethodName(){
        return $this->authentication_method_name;
    }

    public function getAuthorizationMethodName(){
        return $this->authorization_method_name;
    }
}
