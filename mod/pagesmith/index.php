<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

PHPWS_Core::initModClass('pagesmith', 'PageSmith.php');

$pageSmith = new PageSmith;

if (isset($_REQUEST['uop'])) {
    $pageSmith->user();
} elseif (isset($_REQUEST['aop'])) {
    $pageSmith->admin();
} elseif (!empty($_GET['id'])) {
    $pageSmith->viewPage();
} else {
    PHPWS_Core::errorPage('404');
}


?>