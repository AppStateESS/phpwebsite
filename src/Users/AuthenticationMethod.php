<?php
namespace Canopy\Users;

abstract class AuthenticationMethod {

    public function getName(){
        return get_class($this);
    }

}
