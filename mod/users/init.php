<?php

$includeFile = PHPWS_Core::getConfigFile("users", "config.php");
if (PEAR::isError($includeFile)){
  PHPWS_Error::log($includeFile);
  return;
}

if (isset($_REQUEST['action']['admin']))
     PHPWS_Core::initCoreClass("List.php");

include_once $includeFile;

PHPWS_Core::initModClass("users", "Users.php");
if (isset($_REQUEST['action']) && $_REQUEST['module'] == "users")
     PHPWS_Core::initModClass("users", "User_Manager.php");
     else
     PHPWS_Core::killSession("User_Manager");

?>