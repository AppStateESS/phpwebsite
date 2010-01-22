<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

PHPWS_Core::initModClass('categories', 'Action.php');
PHPWS_Core::initModClass('categories', 'Categories.php');

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'admin'){
    Categories_Action::admin();
} else {
    Categories_Action::user();
}

?>