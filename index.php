<?php

/* Show all errors */
error_reporting (E_ALL);

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
include(PHPWS_SOURCE_DIR . "conf/pear_config.php");

/* Load the Core class */
require_once(PHPWS_SOURCE_DIR . "core/Core.php");

PHPWS_Core::initializeModules();

session_start();

//include ("test.php");
PHPWS_Core::runtimeModules();
PHPWS_Core::runCurrentModule();
PHPWS_Core::closeModules();

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

/**
 * loads the config file
 */
function loadConfig($hub_dir=NULL){
  /* Check for config file and define source directory. */
  if(is_file($hub_dir . "conf/db_config.php")){
    include($hub_dir . "conf/db_config.php");
    define("PHPWS_SOURCE_DIR", $source_dir);
    define("CONFIG_FILE", $source_dir . "conf/db_config.php");
  }
  else {
    header("location:setup/set_config.php");
    exit();
  }
}
?>