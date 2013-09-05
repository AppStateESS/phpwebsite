<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if(!file_exists(PHPWS_SOURCE_DIR . 'mod/facheckin/inc/otrs.php')) {
    echo "Please configure facheckin by moving inc/otrs-dist.php to inc/otrs.php first.";
    exit();
}

if (isset($_REQUEST['uop'])) {
    PHPWS_Core::initModClass('facheckin', 'Checkin_User.php');
    $checkin = new Checkin_User;
} elseif (isset($_REQUEST['aop']) || isset($_REQUEST['tab'])) {
    PHPWS_Core::initModClass('facheckin', 'Checkin_Admin.php');
    $checkin = new Checkin_Admin;
} else {
    PHPWS_Core::errorPage('404');
}

$checkin->process();
?>
