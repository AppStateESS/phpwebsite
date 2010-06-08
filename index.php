<?php

/**
 * Main file for loading phpwebsite. Initializes core,
 * checks security, loads modules.
 *
 * @author Matthew McNaney <matt at tux dot appstate edu>
 * @version $Id$
 */
ini_set('register_globals', 0);

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

include PHPWS_SOURCE_DIR . 'phpws_stats.php';

ob_start();
if (is_file(PHPWS_SOURCE_DIR . 'config/core/source.php')) {
    require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
}
require_once PHPWS_SOURCE_DIR . 'inc/Initialization.php';
require_once PHPWS_SOURCE_DIR . 'inc/Forward.php';


Core\Core::requireConfig('core', 'file_types.php');
Core\Core::initializeModules();

define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
session_name(SESSION_NAME);
session_start();

require_once PHPWS_SOURCE_DIR . 'inc/Security.php';

if (!Core\Core::checkBranch()) {
    Core\Core::errorPage();
}

Core\Core::runtimeModules();
Core\Core::checkOverpost();
Core\Core::runCurrentModule();
Core\Core::closeModules();
ob_end_flush();

Core\DB::disconnect();

Core\Core::setLastPost();

if (isset($_REQUEST['reset'])) {
    Core\Core::killAllSessions();
}

show_stats();

?>
