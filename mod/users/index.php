<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}
if (!isset($_REQUEST['action'])) {
    \core\Core::errorPage('404');
}

if (!class_exists('PHPWS_User')){
    \core\Error::log('PHPWS_CLASS_NOT_CONSTRUCTED', 'core', NULL, 'Class: PHPWS_Users');
    return;
}

core\Core::initModClass('users', 'Action.php');

switch ($_REQUEST['action']){
    case 'user':
        User_Action::userAction();
        break;

    case 'admin':
        User_Action::adminAction();
        break;

    case 'permission':
        User_Action::permission();
        break;

    case 'popup_permission':
        User_Action::popupPermission();
        exit();
        break;

    case 'reset':
        $_SESSION['User'] = new PHPWS_User;
        \core\Core::home();
        break;
}// End area switch

?>