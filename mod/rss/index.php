<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}



if ( isset($_REQUEST['command']) && Current_User::allow('rss')) {
    PHPWS_Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
 }


?>