<?php

$includeFile = PHPWS_Core::getConfigFile("layout", "config.php");
include_once $includeFile;

PHPWS_CORE::initModClass("layout", "Layout.php");

?>