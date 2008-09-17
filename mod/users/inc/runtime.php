<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!class_exists('PHPWS_User')) {
    include '../../config/core/404.html';
    exit();
}

if (@$_REQUEST['module'] == 'users' && @$_REQUEST['action'] == 'reset') {
    $_SESSION['User'] = new PHPWS_User;
} else if (!isset($_SESSION['User'])) {
    Current_User::init();
    if (Current_User::allowRememberMe()) {
        if (PHPWS_Settings::get('users', 'allow_remember')) {
            Current_User::rememberLogin();
        }
    }
}

Current_User::getLogin();

?>
