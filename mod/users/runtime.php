<?php

if (!isset($_SESSION['User'])) $_SESSION['User'] = new PHPWS_User;

if (!PHPWS_Layout::get("CNT_user_small")) $_SESSION['User']->getLogin();

?>