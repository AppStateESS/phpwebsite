<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

// If a admin action is called and the user has permissions enter in the admin functions
// otherwise go into user functions
\phpws\PHPWS_Core::initModClass('calendar', 'Calendar.php');

$Calendar = new PHPWS_Calendar;

if ( ( isset($_REQUEST['aop']) || isset($_REQUEST['tab']) ) && Current_User::allow('Calendar') ) {
    $Calendar->admin();
} else {
    $Calendar->user();
}
