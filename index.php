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
mb_internal_encoding('UTF-8');
/**
 * Include the defines used in Global library
 */
require_once 'Config/Defines.php';

/**
 * DISPLAY_ERRORS set in Config/Defines.php
 */
if (DISPLAY_ERRORS) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
} else {
    ini_set('display_errors', 'Off');
}
require_once 'Global/Functions.php';

set_exception_handler(array('Error', 'exceptionHandler'));
if (ERRORS_AS_EXCEPTION) {
    set_error_handler(array('Error', 'errorHandler'));
}

if (is_file('config/core/config.php')) {
require_once 'config/core/config.php';
} else {

}

$controller = new TheThing();
$controller->execute();

// Clean up after ourselves
spl_autoload_unregister('autoloadTheThing');
restore_exception_handler();
restore_error_handler();


?>
