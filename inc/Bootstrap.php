<?php

/**
 * Require this file to bootstrap phpWebSite.
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

mb_internal_encoding('UTF-8');

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

function PHPWS_unBootstrap() {
    restore_exception_handler();
    restore_error_handler();
    spl_autoload_unregister('phpwsAutoload');
}

?>
