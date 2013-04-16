<?php

/**
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
mb_internal_encoding('UTF-8');
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

define('CODE_PATH', __DIR__);
chdir(CODE_PATH);

session_start();

require_once 'Global/Functions.php';
set_exception_handler(array('Error', 'exceptionHandler'));
if (ERRORS_AS_EXCEPTION) {
    set_error_handler(array('Error', 'errorHandler'));
}

if (isset($_SESSION['Loop_Stop'])) {
    $_SESSION['Loop_Stop']++;
    if ($_SESSION['Loop_Stop'] > 3) {
        $e = new \Exception(t('Recursive loop limit passed'));
        \Error::log($e);
        \Error::errorPage();
    }
}
// set in Defines.php
require_once CONFIGURATION_FILE;

// catch any echoed content
ob_start();
$manager = ModuleManager::singleton();
$manager->run();
// pipe any captured content into Body before display
$content = trim(ob_get_contents());
if (!empty($content)) {
    Body::add($content);
}
ob_end_clean();
Body::show();
?>
