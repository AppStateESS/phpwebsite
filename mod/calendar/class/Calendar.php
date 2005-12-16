<?php

  /**
   * Main command class for Calendar module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('calendar', 'View.php');
PHPWS_Core::requireConfig('calendar');

if (!defined('CALENDAR_MONTH_LISTING')) {
    define('CALENDAR_MONTH_LISTING', '%B');
 }


class PHPWS_Calendar {
    var $today    = NULL;
    var $month    = NULL;
    // object for controlling user requests
    var $user     = NULL;

    // object controlling administrative requests
    var $admin    = NULL;

    // view object for displaying calendars
    var $view         = NULL;

    function PHPWS_Calendar() {
        // using server time
        $this->today = PHPWS_Time::mkservertime();
    }


    function loadSchedule()
    {
        PHPWS_Core::initModClass('calendar', 'Schedule.php');
        if (isset($_REQUEST['schedule_id'])) {
            $this->schedule = & new Calendar_Schedule($_REQUEST['schedule_id']);
        } else {
            $this->schedule = & new Calendar_Schedule;
        }
        $this->schedule->calendar = & $this;
    }

    /**
     * Directs the user (non-admin) functions for calendar
     */
    function user()
    {
        if (isset($_REQUEST['view'])) {
            $this->loadView();
            $this->view->main();
        } elseif (isset($_REQUEST['uop'])) {
            PHPWS_Core::initModClass('calendar', 'User.php');
            $this->user = & new Calendar_User;
            $this->user->calendar = & $this;
            $this->user->main();
        }
    }

    /**
     * Directs the administrative functions for calendar
     */
    function admin()
    {
        PHPWS_Core::initModClass('calendar', 'Admin.php');
        $Calendar->admin = & new Calendar_Admin;
        $Calendar->admin->calendar = & $this;
        $Calendar->admin->main();
    }


    function checkDate($date)
    {
        if ( empty($date) || $date < gmmktime(0,0,0, 1, 1, 1970)) {
            $date = & $this->today;
        }

        return $date;
    }

    function loadView()
    {
        $this->view = & new Calendar_View;
        $this->view->calendar = & $this;
    }

    
    function &getMonth($month=NULL, $year=NULL)
    {
        require_once 'Calendar/Month/Weekdays.php';
        if (!isset($month)) {
            $month = date('m');
        }

        if (!isset($year)) {
            $year = date('Y');
        }

        $oMonth = & new Calendar_Month_Weekdays($year, $month, PHPWS_Settings::get('calendar', 'starting_day'));
        $oMonth->build();
        return $oMonth;
    }

    // Checks the user cookie for a hour format
    function userHourFormat()
    {
        $hour_format = PHPWS_Cookie::read('calendar', 'hour_format');

        if (empty($hour_format)) {
            return PHPWS_Settings::get('calendar', 'default_hour_format');
        } else {
            return $hour_format;
        }
    }

    function getMonthArray()
    {
        for ($i=1; $i < 13; $i++) {
            $months[$i] = strftime(CALENDAR_MONTH_LISTING, $i);
        }

        return $months;
    }

    function getDayArray()
    {
        for ($i=1; $i < 32; $i++) {
            $days[$i] = $i;
        }

        return $days;
    }

    function getYearArray()
    {
        $year_start = (int)date('Y') - 2;
        $year_end = $year_start + 11;
        
        for ($i = $year_start; $i < $year_end; $i++) {
            $years[$i] = $i;
        }

        return $years;
    }
}

?>