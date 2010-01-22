<?php

/**
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @version $Id$
 */

if(!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('link', 'LinkController.php');
$controller = LinkController::getInstance();
$controller->process();

?>
