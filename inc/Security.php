<?php

/**
 * This file should contain any security measures made
 * against user submissions.
 */

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
clearRequest();
function clearRequest()
{
  $scriptPattern = array('/(%3C|<|&lt;|&#60;)\s*(script|\?)/iU');
  
  foreach ($_REQUEST as $key => $value) {
    $_REQUEST[$key] = preg_replace($scriptPattern, '', strip_tags($value));
    if (isset($_GET[$key])) {
      $_GET[$key] = $_REQUEST[$key];
    }

    if (isset($_POST[$key])) {
      $_POST[$key] = $_REQUEST[$key];
    }
  }
}

?>
