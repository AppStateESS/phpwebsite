<?php

  /**
   * Main file for loading phpwebsite. Initializes core,
   * checks security, loads modules.
   *
   * @author Matthew McNaney <matt at tux dot appstate edu>
   * @version $Id$
   */

ini_set('register_globals', 0);
include 'phpws_stats.php';

// For extra security, consider changing AUTO_ROUTE to FALSE
// after installation

define('AUTO_ROUTE', TRUE);

if (is_file('config/core/config.php')) {
    require_once 'config/core/config.php';
 } else {
    if (AUTO_ROUTE == TRUE) {
        if (is_file('./setup/index.php')) {
            header('Location: ./setup/index.php');
            exit();
        } else {
            exit('Fatal Error: Could not locate your configuration file.');
        }
    } else {
        exit('Fatal Error: Could not locate your configuration file.');
    }
 }

require_once PHPWS_SOURCE_DIR . 'inc/Functions.php';

ob_start();

require_once PHPWS_SOURCE_DIR . 'core/class/Init.php';
require_once PHPWS_SOURCE_DIR . 'inc/Forward.php';
require_once PHPWS_SOURCE_DIR . 'inc/Security.php';

PHPWS_Core::requireConfig('core', 'file_types.php');
PHPWS_Core::checkSecurity();
PHPWS_Core::initializeModules();

session_name(SESSION_NAME);
session_start();

if (!PHPWS_Core::checkBranch()) {
    PHPWS_Core::errorPage();
}

PHPWS_Core::runtimeModules();
PHPWS_Core::checkOverpost();
PHPWS_Core::runCurrentModule();
PHPWS_Core::closeModules();
ob_end_flush();

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

if (isset($_REQUEST['reset'])) {
    PHPWS_Core::killAllSessions();
 }

show_stats();

?>
