<?php

PHPWS_Core::configRequireOnce("users", "config.php", TRUE);
PHPWS_Core::configRequireOnce("users", "errorDefines.php", TRUE);

include_once $includeFile;

PHPWS_Core::initModClass("users", "Users.php");
PHPWS_Core::initModClass("users", "Current_User.php");
PHPWS_Text::addTag('users', 'new_account');

?>