<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: runtime.php 7776 2010-06-11 13:52:58Z jtickle $
 */
if (!class_exists('PHPWS_User')) {
    include '../../core/conf/404.html';
    exit();
}

if ((isset($_REQUEST['module']) && $_REQUEST['module'] == 'users') && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'reset')) {
    $_SESSION['User'] = new PHPWS_User;
} elseif (!isset($_SESSION['User'])) {
    Current_User::init();
    if (Current_User::allowRememberMe()) {
        if (PHPWS_Settings::get('users', 'allow_remember')) {
            Current_User::rememberLogin();
        }
    }
}

Current_User::loadAuthorization($_SESSION['User']);
Current_User::getLogin();
?>
