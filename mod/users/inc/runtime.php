<?php

if (!class_exists("PHPWS_User"))
     return;

if (!isset($_SESSION['User']))
     Current_User::logAnonymous();

if (!Layout::isBoxSet("users", "CNT_user_small")) Current_User::getLogin();

?>