<?php

list($usec, $sec) = explode(" ", microtime());
$site_start_time = ((float)$usec + (float)$sec);

// REMOVE !
define("AUTO_ROUTE", TRUE);

if (is_file('config/core/config.php')) require_once 'config/core/config.php';
else {
  if (AUTO_ROUTE == TRUE){
    header('location:http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'setup/');
    exit();
  } else
    exit('Fatal Error: Could not locate your configuration file.');
}

require_once PHPWS_SOURCE_DIR . 'inc/Functions.php';

/* Show all errors */
error_reporting (E_ALL);

ob_start();


require_once PHPWS_SOURCE_DIR . 'inc/Security.php';
require_once PHPWS_SOURCE_DIR . 'core/class/Init.php';

PHPWS_Core::checkSecurity();
PHPWS_Core::initializeModules();

session_name(SESSION_NAME);
session_start();

checkJavascript();

PHPWS_Core::runtimeModules();
PHPWS_Core::runCurrentModule();
PHPWS_Core::closeModules();
ob_end_flush();

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

if (isset($_REQUEST['reset']))
     PHPWS_Core::killAllSessions();

list($usec, $sec) = explode(' ', microtime());
$site_end_time = ((float)$usec + (float)$sec);

$memory_used = round( (memory_get_usage() / 1024) / 1024, 2);
$execute_time = round( ($site_end_time - $site_start_time), 2);

//echo "$memory_used mb / $execute_time secs";

?>
