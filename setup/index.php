<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
ini_set('display_errors', 'On');
error_reporting(-1);
chdir('../');
define('PHPWS_SOURCE_DIR', getcwd() . '/');
define('PHPWS_SOURCE_HTTP', './');

define('SETUP_USER_ERROR', -1);
define('SITE_HASH', 'x');
define('SETUP_CONFIGURATION_DIRECTORY', 'config/');


//require_once 'core/conf/defines.dist.php';
require_once 'core/conf/defines.php';
require_once 'Global/Functions.php';
require_once 'setup/class/Setup.php';

set_exception_handler(array('Error', 'exceptionHandler'));

try {
    $setup = new Setup;
    $setup->initialize();
    $setup->processCommand();
} catch (\Exception $e) {
    if ($e->getCode() == SETUP_USER_ERROR) {
        $setup->setMessage($e->getMessage());
    } else {
        throw $e;
    }
}
$setup->display();
?>