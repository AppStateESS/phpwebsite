<?php

  /**
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   * @version $Id: index.php 5472 2007-12-11 16:13:40Z jtickle $
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('poll', 'PollController.php');
$controller = PollController::getInstance();
$controller->process();

?>
