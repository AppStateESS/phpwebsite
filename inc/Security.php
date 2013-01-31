<?php

/**
 * This file should contain any security measures made
 * against user submissions.

 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}

// Moodle does not play nice with phpWebSite when installed on the
// same domain.  Here is a hack to make sure the MOODLEID cookies
// aren't a problem.
// TODO: Find out why our security code chokes on some Moodle
//       cookies and fix it.
$known_bad = array('MOODLEID', 'MOODLEID_');
foreach($known_bad as $bad) {
    if(isset($_REQUEST[$bad]) && !checkUserInput($_REQUEST[$bad])) {
        Security::log("Apparently butting heads with another web application. $bad='{$_REQUEST[$bad]}'");
        unset($_REQUEST[$bad]);
    }
}

/**
 * stripslashes_deep is from aderyn (gmail.com) on php.net
 */
if (get_magic_quotes_gpc())
{
    if (!empty($_REQUEST)) {
        $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        if (!empty($_GET)) {
            $_GET = array_map('stripslashes_deep', $_GET);
        }

        if (!empty($_POST)) {
            $_POST = array_map('stripslashes_deep', $_POST);
        }
    }
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

// Attempt to clean out the xss tags

if (!(PHPWS_Core::allowScriptTags()) &&
(!checkUserInput($_SERVER['REQUEST_URI']) || !checkUserInput($_REQUEST))) {
    Security::log(_('Attempted cross-site scripting attack.'));
    PHPWS_Core::errorPage('400');
}

/**
 * Checks for <script> embedding and any double-URL-encoded data
 * 
 * @return bool
 */
function checkUserInput($input)
{
    $scripting = '/(%3C|<|&lt;|&#60;)\s*(script|\?)/iU';
    $asciiChars = '/%(0|1)(\d|[a-f])/i';

    // Call recursively if input is an array
    if (is_array($input)) {
        foreach ($input as $input_val) {
            if (!checkUserInput($input_val)) {
                return FALSE;
            }   
        }   
        return TRUE;
    } else {

        // Decoding input once is ok
        $decodedInput = rawurldecode($input);

        // Check for any script tags or any remaining URL encoded characters
        if (preg_match($scripting, $decodedInput) || preg_match($asciiChars, $decodedInput)) {
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
