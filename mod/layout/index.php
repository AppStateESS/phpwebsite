<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    Error::errorPage(403);
}

if ($_REQUEST['module'] != 'layout' || !isset($_REQUEST['action'])) {
    Error::errorPage('404');
}


if ($_REQUEST['action'] == 'ckeditor') {
    Layout::ckeditor();
    exit();
}

if (!Current_User::allow('layout')) {
    Current_User::disallow();
}

PHPWS_Core::initModClass('layout', 'LayoutAdmin.php');

switch ($_REQUEST['action']){
    case 'admin':
        Layout_Admin::admin();
        break;

    default:
        PHPWS_Core::errorPage('404');
} // END action switch

?>
