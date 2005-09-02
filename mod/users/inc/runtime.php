<?php
PHPWS_Settings::load('users');

if (!class_exists("PHPWS_User")){
  return;
}

if (!isset($_SESSION['User'])){
  $_SESSION['User'] = & new PHPWS_User;
}

Current_User::getLogin();

?>
