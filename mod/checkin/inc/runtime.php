<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (core\Core::atHome() && \core\Settings::get('checkin', 'front_page')) {
    \core\Core::initModClass('checkin', 'Checkin_User.php');
    $checkin = new Checkin_User;
    $checkin->process('checkin_form');
}


if (Current_User::allow('checkin')) {
    \core\Core::initModClass('checkin', 'Checkin_Admin.php');
    $checkin_admin = new Checkin_Admin;
    $checkin_admin->menu();
}

?>