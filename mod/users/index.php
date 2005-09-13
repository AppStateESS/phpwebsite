<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if (!isset($_REQUEST['action'])) return;

if (!class_exists('PHPWS_User')){
     PHPWS_Error::log('PHPWS_CLASS_NOT_CONSTRUCTED', 'core', NULL, 'Class: PHPWS_Users');
     return;
}

PHPWS_Core::initModClass('users', 'Action.php');

switch ($_REQUEST['action']){
 case 'user':
   User_Action::userAction();
   break;

 case 'admin':
   User_Action::adminAction();
   break;
}// End area switch

?>