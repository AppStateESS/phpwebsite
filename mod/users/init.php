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

?>