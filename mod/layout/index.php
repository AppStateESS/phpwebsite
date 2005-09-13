<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if ($_REQUEST['module'] != 'layout' || !isset($_REQUEST['action']))
  exit();

PHPWS_Core::initModClass('layout', 'LayoutAdmin.php');

switch ($_REQUEST['action']){
 case 'admin':
   Layout_Admin::admin();
   break;
} // END action switch


?>
