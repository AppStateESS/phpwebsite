<?php

class Current_User {

  function init($id){
    $_SESSION['User'] = new PHPWS_User($id);
    $_SESSION['User']->setLogged(TRUE);
    Current_User::updateLastLogged();
    Current_User::getLogin();
  }
  
  function allow($itemName, $subpermission=NULL, $item_id=NULL){
    return $_SESSION['User']->allow($itemName, $subpermission, $item_id);
  }

  function getLogin(){
    PHPWS_Core::initModClass("users", "Form.php");
    $login = User_Form::logBox();
    Layout::set($login, "users", "CNT_user_small");
  }

  function logAnonymous(){
    PHPWS_Core::initModClass("users", "Action.php");
    $_SESSION['User'] = new PHPWS_User(1);
  }

  function isDeity(){
    return $_SESSION['User']->isDeity();
  }

  function getId(){
    return $_SESSION['User']->getId();
  }

  function updateLastLogged(){
    $db = & new PHPWS_DB("users");
    $db->addWhere("id", $_SESSION['User']->getId());
    $db->addValue("last_logged", mktime());
    return $db->update();
  }

  function getUsername(){
    return $_SESSION['User']->getUsername();
  }

  function isLogged(){
    return $_SESSION['User']->isLogged();
  }

  function save(){
    return $_SESSION['User']->save();
  }

}

?>