<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('pagesmith', 'PageSmith.php');

$pageSmith = new PageSmith;

if (isset($_GET['var1'])) {
    $_REQUEST['id'] = $_GET['id'] = (int)$_GET['var1'];
}

if (isset($_REQUEST['uop'])) {
    $pageSmith->user();
} elseif (isset($_REQUEST['aop'])) {
    $pageSmith->admin();
} elseif ($_GET['id']) {
    $pageSmith->viewPage();
}


?>