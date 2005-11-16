<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if ( ( isset($_REQUEST['command']) || isset($_REQUEST['tab']) ) && Current_User::allow('rss')) {
    PHPWS_Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
 } elseif ($_REQUEST['mod_title']) {
     PHPWS_Core::initModClass('rss', 'RSS.php');
     RSS::viewChannel($_REQUEST['mod_title']);
 }


?>