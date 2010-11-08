<?php

/**
 * Main file for loading phpwebsite. Initializes core,
 * checks security, loads modules.
 *
 * @author Matthew McNaney <matt at tux dot appstate edu>,
 * @author Hilmar Runge <hi at dc4db dot net>
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

// Commented out in phpWebSite 2.0. If missing functions, uncomment
// require_once PHPWS_SOURCE_DIR . 'inc/Functions.php';

ob_start();
require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
require_once PHPWS_SOURCE_DIR . 'core/class/Init.php';
require_once PHPWS_SOURCE_DIR . 'inc/Forward.php';


PHPWS_Core::requireConfig('core', 'file_types.php');
PHPWS_Core::initializeModules();

define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
session_name(SESSION_NAME);
session_start();

require_once PHPWS_SOURCE_DIR . 'inc/Security.php';

if (!PHPWS_Core::checkBranch()) {
    PHPWS_Core::errorPage();
}

PHPWS_Core::runtimeModules();
PHPWS_Core::checkOverpost();
PHPWS_Core::runCurrentModule();
PHPWS_Core::closeModules();

// BGmode (
if (isset($_SESSION['BG'])) {
    ob_end_clean();
    echo $_SESSION['BG'];
} else {
    ob_end_flush();
}
// )

PHPWS_DB::disconnect();

PHPWS_Core::setLastPost();

if (isset($_REQUEST['reset'])) {
    PHPWS_Core::killAllSessions();
}
// BGmode(
if (isset($_SESSION['BG'])) {
    unset($_SESSION['BG']);
} else {
    show_stats();
}
// )

?>
