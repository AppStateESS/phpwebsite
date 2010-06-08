<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$mini_cal_display = Core\Settings::get('calendar', 'display_mini');

if ($mini_cal_display == MINI_CAL_SHOW_ALWAYS ||
($mini_cal_display == MINI_CAL_SHOW_FRONT && Core\Core::atHome())) {
    Layout::addStyle('calendar');

    $Calendar = new PHPWS_Calendar;
    $Calendar->loadUser();
    if (Core\Settings::get('calendar', 'mini_grid')) {
        $lil_calendar = $Calendar->user->mini_month();
        Layout::add($lil_calendar, 'calendar', 'minimonth');
    }

    $upcoming = $Calendar->user->upcomingEvents();

    if ($upcoming) {
        Layout::add($upcoming, 'calendar', 'upcoming');
    }
}

?>
