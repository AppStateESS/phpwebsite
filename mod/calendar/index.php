<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

// If a admin action is called and the user has permissions enter in the admin functions
// otherwise go into user functions

$Calendar = & new PHPWS_Calendar;

if ( ( isset($_REQUEST['a_action']) || isset($_REQUEST['tab']) ) && Current_User::allow('Calendar') ) {
    $Calendar->admin();
 } else {
    $Calendar->user();
 } 
?>