<?php

class User_Manager extends PHPWS_User{

  function listActive(){
    if ($this->isActive())
      return _("Yes");
    else
      return _("No");
  }

  function listLastLogged(){
    $logged = $this->getLastLogged("%c");

    if (empty($logged))
      return _("Never");
    else
      return $logged;

  }

}

?>