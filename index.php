<?php

/* Show all errors */
error_reporting (E_ALL);

ob_start();
/* Determine if this is a hub or branch site and load the
 * appropiate config file. $hub_dir will be included if
 * coming from a branch site
 */

if (!isset($hub_dir)){
     $branchName = $hub_dir = NULL;
}
loadConfig($hub_dir);

/* Security against those with register globals = on */
if (ini_get('register_globals')){
  foreach ($_REQUEST as $requestVarName=>$nullIT)
    unset($requestVarName);
  unset($nullIT);
}

/* Loads Pear config file */
include PHPWS_SOURCE_DIR . "conf/pear_config.php";

/* Initialize core defines */
require_once PHPWS_SOURCE_DIR . "class/Init.php";

ob_end_flush();

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

PHPWS_Core::report();

if (isset($_REQUEST['reset']))
     PHPWS_Core::killAllSessions();

/**
 * loads the config file
 */
function loadConfig($hub_dir=NULL){
  /* Check for config file and define source directory. */
  if(is_file($hub_dir . "conf/config.php")){
    include($hub_dir . "conf/config.php");
    define("PHPWS_SOURCE_DIR", $source_dir);
    define("CONFIG_FILE", $source_dir . "conf/config.php");
    define("SESSION_NAME", md5($site_hash));
  }
  else {
    header("location:setup/set_config.php");
    exit();
  }
}

?>