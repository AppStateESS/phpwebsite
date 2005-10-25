<?php

  /**
   * Steering file
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}


if (isset($_REQUEST['tab']) || isset($_REQUEST['command'])) {
    Search_Admin::main();
 } else {
    Search_User::main();
 }


?>