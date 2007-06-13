<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
 }

$idk = new Idkhtml;

if (isset($_REQUEST['uop'])) {
    $idk->user();
} elseif (isset($_REQUEST['aop'])) {
    $idk->admin();
} elseif ($_GET['id']) {
    $idk->viewPage();
}


?>