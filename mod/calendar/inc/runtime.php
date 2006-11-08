<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$mini_cal_display = PHPWS_Settings::get('calendar', 'display_mini');

if ($mini_cal_display == MINI_CAL_SHOW_ALWAYS ||
    ($mini_cal_display == MINI_CAL_SHOW_FRONT && PHPWS_Core::atHome())) {
    $cal_key = sprintf('cal_%s_%s', date('m'), date('Y'));
    $lil_calendar = PHPWS_Cache::get($cal_key);

    if (empty($lil_calendar)) {
        $Calendar = & new PHPWS_Calendar;
        $Calendar->loadUser();
        $lil_calendar = $Calendar->user->mini_month();
        PHPWS_Cache::save($cal_key, $lil_calendar);
        unset($Calendar);
    } else {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }
    }

    Layout::add($lil_calendar, 'calendar', 'minimonth');
 }

?>
