<?php

/**
 * Require this file to bootstrap phpWebSite.
 * @author Matthew McNaney <mcnaneym at appstate dot edu>
 * @author Jeff Tickle <ticklejw at appstate dot edu>
 */
/* * *
 * Character Encoding
 *
 * This is part of the 'mbstring' extension and is not enabled by default.
 * See this page for installation instructions:
 * http://www.php.net/manual/en/mbstring.installation.php
 */

/*
 * NB: This is redundant, it happens in index.php
if (is_file('config/core/config.php')) {
    require_once 'config/core/config.php';
}
*/

if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
}

/* * * Include System-wide Defines ** */
if (file_exists(PHPWS_HOME_DIR . 'config/phpws/defines.php')) {
    require_once(PHPWS_HOME_DIR . 'config/phpws/defines.php');
} elseif (file_exists(PHPWS_SOURCE_DIR . 'config/phpws/defines.php')) {
    require_once(PHPWS_SOURCE_DIR . 'config/phpws/defines.php');
} else {
    require_once(PHPWS_SOURCE_DIR . 'src-phpws-legacy/config/defines.php');
}

/* * *
 * Error Display and Reporting *
 * DISPLAY_ERRORS is defined in config/defines.php
 */
if (DISPLAY_ERRORS) {
    // For development - show all errors
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
} else {
    // Production - Don't display any errors to the end user
    ini_set('display_errors', 'Off');
    // Report all fatal types of errors (this allows them to be logged to the web server)
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
}

// Include the autoloaders
require_once PHPWS_SOURCE_DIR . 'src/Autoloader.php';

/*** Exception Handler ***/
require_once PHPWS_SOURCE_DIR . 'src-phpws2/src/Error.php';
set_exception_handler(array('\phpws2\Error', 'exceptionHandler'));

if (ERRORS_AS_EXCEPTION) {
    set_error_handler(array('\phpws2\Error', 'errorHandler'));
}

function PHPWS_unBootstrap()
{
    restore_exception_handler();
    restore_error_handler();
}
