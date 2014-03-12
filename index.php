<?php

/**
 * Main file for loading phpwebsite. Loads configuration
 * and creates inital object to start execution.
 *
 * @link http://phpwebsite.appstate.edu/
 * @package phpws
 * @author Matthew McNaney <matt at tux dot appstate dot edu>,
 * @author Hilmar Runge <hi at dc4db dot net>
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU GPLv3
 * @copyright Copyright 2013, Appalachian State University & Contributors
 */

/****
 * Begin output buffering right away.
 * This is important to prevent stray output
 * from being included in AJAX/JSON responses.
 */
ob_start();


/**
 * Include the defines used in Global library
 */
if (is_file('config/core/config.php')) {
    require_once 'config/core/config.php';
} else {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '',
                    $_SERVER['PHP_SELF']) . 'setup/index.php';
    echo 'Configuration file not found. <a href="' . $url . '">Continue to setup</a>.';
    exit();
}

require_once(PHPWS_SOURCE_DIR . 'inc/Bootstrap.php');
loadTimeZone();
$request = \Server::getCurrentRequest();
$controller = new PhpwebsiteController();
$controller->execute($request);


/**
 * "BG Mode" - Used to echo raw output from the session,
 * usually for AJAX requests contaning JSON.
 * @deprecated - Will be removed in the next *major* release (version 2.0.0).
 * @see ModuleController
 */
if (isset($_SESSION['BG'])) {
    ob_end_clean();         // Stop output buffering and clear the buffer, without outputting anything
    echo $_SESSION['BG'];   // Echo any json data from the session
    unset($_SESSION['BG']); // Clear the session'd data (for next request)
} else {
    ob_end_flush();
}

PHPWS_unBootstrap();
?>
