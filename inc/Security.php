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

?>
