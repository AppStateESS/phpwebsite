<?php

$includeFile = PHPWS_Core::getConfigFile("users", "config.php");
if (PEAR::isError($includeFile)){
  PHPWS_Error::log($includeFile);
  return;
}

include_once $includeFile;

PHPWS_Core::initModClass("users", "ModSetting.php");
PHPWS_Core::initModClass("users", "Users.php");

?>