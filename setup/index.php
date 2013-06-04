<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
ini_set('display_errors', 'On');
error_reporting (-1);
chdir('../');
define('PHPWS_SOURCE_DIR', getcwd() . '/');

require_once 'Global/Functions.php';
require_once 'setup/class/Setup.php';

$setup = new Setup;

$setup->initialize();

if ($setup->isAdminLoggedIn()) {
    $setup->processCommand();
} else {
    $setup->showLoginForm();
}

$setup->display();

?>