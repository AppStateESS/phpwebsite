<?php

require_once "config/core/config.php";

/* Show all errors */
error_reporting (E_ALL);

ob_start();

/* Security against those with register globals = on */
if (ini_get('register_globals')){
  foreach ($_REQUEST as $requestVarName=>$nullIT)
    unset($requestVarName);
  unset($nullIT);
}

/* Initialize core defines */
require_once PHPWS_SOURCE_DIR . "class/Init.php";

ob_end_flush();

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

PHPWS_Core::report();

if (isset($_REQUEST['reset']))
     PHPWS_Core::killAllSessions();

?>