<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (PHPWS_Core::atHome() && PHPWS_Settings::get('checkin', 'front_page')) {
    PHPWS_Core::initModClass('checkin', 'Checkin_User.php');
    $checkin = new Checkin_User;
    $checkin->checkinForm();
}

?>