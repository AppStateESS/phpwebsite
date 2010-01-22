<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

if(!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('fancypants', 'FancypantsController.php');
$controller = FancypantsController::getInstance();
$controller->main();

?>
