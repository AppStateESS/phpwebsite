<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('filecabinet', 'Cabinet_Action.php');
if (!isset($_REQUEST['tab']) && !isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    Cabinet_Action::download($_REQUEST['id']);
 } else {
    Cabinet_Action::admin();
 }

?>