<?php

$includeFile = PHPWS_Core::getConfigFile("layout", "config.php");
if (PEAR::isError($includeFile))
     echo PHPWS_Error::printError($includeFile);
     else
     include_once $includeFile;

PHPWS_CORE::initModClass("layout", "Layout.php");

?>