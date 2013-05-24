<?php

/**
 * Require this file to bootstrap phpWebSite.
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */


/***
 * This is part of the 'mbstring' extension and is not enabled by default.
 * See this page for installation instructions:
 * http://www.php.net/manual/en/mbstring.installation.php
 */
if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
}


/**
 * Include the defines used in Global library
 */
if (is_file('config/core/config.php')) {
    require_once 'config/core/config.php';
} else {
    header('location: setup/index.php');
    exit();
}
//require_once 'config/Defines.php';
/**
 * DISPLAY_ERRORS set in Config/Defines.php
 */
if (file_exists(PHPWS_SOURCE_DIR . 'core/conf/defines.php')) {
    require_once(PHPWS_SOURCE_DIR . 'core/conf/defines.php');
} else {
    require_once(PHPWS_SOURCE_DIR . 'core/conf/defines.dist.php');
}
date_default_timezone_set(DATE_SET_SERVER_TIME_ZONE);

if (DISPLAY_ERRORS) {
    ini_set('display_errors', 'On');
    error_reporting(-1);
} else {
    ini_set('display_errors', 'Off');
    error_reporting(0);
}
require_once PHPWS_SOURCE_DIR . 'Global/Functions.php';

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


function PHPWS_unBootstrap()
{
    restore_exception_handler();
    restore_error_handler();
    spl_autoload_unregister('phpwsAutoload');
}

?>
