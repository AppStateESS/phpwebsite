<?php

if (!isset($_SESSION['User']))
     PHPWS_User::logAnonymous();

if (!PHPWS_Layout::get("CNT_user_small")) PHPWS_User::getLogin();

?>