<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (isset($_REQUEST['uop'])) {
    \phpws\PHPWS_Core::initModClass('checkin', 'Checkin_User.php');
    $checkin = new Checkin_User;
} elseif (isset($_REQUEST['aop']) || isset($_REQUEST['tab'])) {
    \phpws\PHPWS_Core::initModClass('checkin', 'Checkin_Admin.php');
    $checkin = new Checkin_Admin;
} else {
    \phpws\PHPWS_Core::errorPage('404');
}

$checkin->process();
