<?php

if (!class_exists("PHPWS_User")){
  return;
}

if (!isset($_SESSION['User'])){
  Current_User::logAnonymous();
}

Current_User::getLogin();

?>