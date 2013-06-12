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

$controller = ModuleController::singleton();
$controller->execute();

PHPWS_unBootstrap();
?>
