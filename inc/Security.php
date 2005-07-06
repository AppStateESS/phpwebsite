<?php

/**
 * This file should contain any security measures made
 * against user submissions.
 */


/**
 * stripslashes_deep is from aderyn (gmail.com) on php.net
 */
if (get_magic_quotes_gpc())
{
    $_GET    = array_map('stripslashes_deep', $_GET);
    $_POST  = array_map('stripslashes_deep', $_POST);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}


/* Security against those with register globals = on */
if (ini_get('register_globals')) {
    ini_set('register_globals', FALSE);
    foreach ($_REQUEST as $requestVarName=>$nullIT) {
        unset($requestVarName);
    }
    unset($nullIT);
}

/* Attempts to turn off use_trans_sid if enabled */
if (ini_get('session.use_trans_sid')) {
    ini_set('session.use_trans_sid', FALSE);
    ini_set('url_rewriter.tags', '');
}

define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));

// Attempt to clean out the xss tags

if (!checkUserInput($_SERVER['REQUEST_URI']) || !checkUserInput($_REQUEST)) {
    Security::log(_('Attempted cross-site scripting attack.'));
    PHPWS_Core::errorPage(404);
}

function checkUserInput($check)
{
    $scripting = '/(%3C|<|&lt;|&#60;)\s*(script|\?)/iU';
    $ascii_chars = '/%([0-2]\d|3[0-1]|\d\D)/';

    if (is_array($check)) {
        foreach ($check as $check_val) {
            if (!checkUserInput($check_val)) {
                return FALSE;
            }
        }
        return TRUE;
    } else {

        if (preg_match($scripting, $check) ||
            preg_match($ascii_chars, $check)) {
            return FALSE;
        }
        return TRUE;
    }
}


function stripslashes_deep($value)
{
    return (is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value));
}

?>
