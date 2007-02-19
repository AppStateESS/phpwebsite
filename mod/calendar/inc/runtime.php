<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$mini_cal_display = PHPWS_Settings::get('calendar', 'display_mini');

if ($mini_cal_display == MINI_CAL_SHOW_ALWAYS ||
    ($mini_cal_display == MINI_CAL_SHOW_FRONT && PHPWS_Core::atHome())) {
    translate('calendar');
    $Calendar = new PHPWS_Calendar;
    $Calendar->loadUser();
    $lil_calendar = $Calendar->user->mini_month();
    
    Layout::add($lil_calendar, 'calendar', 'minimonth');
    translate();
 }

?>
