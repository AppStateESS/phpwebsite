<?php

$includeFile = PHPWS_Core::getConfigFile("layout", "config.php");

if (PEAR::isError($includeFile)) PHPWS_Error::log($includeFile);
else include_once $includeFile;

PHPWS_CORE::initModClass("layout", "Layout.php");

?>