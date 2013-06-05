<?php

/**
 * Require this file to bootstrap phpWebSite.
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */


/***
 * Character Encoding
 *
 * This is part of the 'mbstring' extension and is not enabled by default.
 * See this page for installation instructions:
 * http://www.php.net/manual/en/mbstring.installation.php
 */
if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
}

/*** Include System-wide Defines ***/
if (file_exists(PHPWS_SOURCE_DIR . 'core/conf/defines.php')) {
    require_once(PHPWS_SOURCE_DIR . 'core/conf/defines.php');
} else {
    require_once(PHPWS_SOURCE_DIR . 'core/conf/defines.dist.php');
}

/*** Time Zone Setting ***/
date_default_timezone_set(DATE_SET_SERVER_TIME_ZONE);

/***
 * Error Display and Reporting *
 * DISPLAY_ERRORS is defined in config/defines.php
 */
if (DISPLAY_ERRORS) {
    // For devleopment - show all errors
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
} else {
    // Production - Don't display any errors to the end user
    ini_set('display_errors', 'Off');
    // Report all fatal types of errors (this allows them to be logged to the web server)
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
}

/*** Include a bunch of function ***/
/*** TODO: See Issue #94 ***/
require_once PHPWS_SOURCE_DIR . 'Global/Functions.php';

/*** Exception Handler ***/
set_exception_handler(array('Error', 'exceptionHandler'));
if (ERRORS_AS_EXCEPTION) {
    set_error_handler(array('Error', 'errorHandler'));
}

require_once PHPWS_SOURCE_DIR . 'Global/Implementations.php';
require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
require_once PHPWS_SOURCE_DIR . 'inc/Security.php';
PHPWS_Core::checkOverpost();
PHPWS_Core::setLastPost();

Language::setLocale(Settings::get('Global', 'language'));
if (!PHPWS_Core::checkBranch()) {
    throw new Exception('Unknown branch called');
}

function PHPWS_unBootstrap()
{
    restore_exception_handler();
    restore_error_handler();
    spl_autoload_unregister('phpwsAutoload');
}

?>
