<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if ($_REQUEST['module'] != 'layout' || !isset($_REQUEST['action'])) {
     PHPWS_Core::errorPage('404');
 }

PHPWS_Core::initModClass('layout', 'LayoutAdmin.php');

switch ($_REQUEST['action']){
 case 'admin':
   Layout_Admin::admin();
   break;

 default:
     PHPWS_Core::errorPage('404');
} // END action switch


?>
