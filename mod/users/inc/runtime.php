<?php

if (!class_exists("PHPWS_User"))
     return;

if (!isset($_SESSION['User']))
     PHPWS_User::logAnonymous();

if (!Layout::get("CNT_user_small")) PHPWS_User::getLogin();

?>