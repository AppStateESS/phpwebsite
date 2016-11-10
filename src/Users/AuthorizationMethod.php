<?php
namespace Canopy\Users;

abstract class AuthorizationMethod {

    public function getName(){
        return get_class($this);
    }

}
