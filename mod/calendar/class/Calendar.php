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

define('MINI_CAL_NO_SHOW', 1);
define('MINI_CAL_SHOW_FRONT', 2);
define('MINI_CAL_SHOW_ALWAYS', 3);


class PHPWS_Calendar {
    var $today        = 0;
    var $month        = 0;
    var $day          = 0;
    var $year         = 0;
    var $request_date = 0;
    var $current_view = DEFAULT_CALENDAR_VIEW;

    // object for controlling user requests
    var $user     = NULL;

    // object controlling administrative requests
    var $admin    = NULL;

    // view object for displaying calendars
    var $view         = NULL;

    function PHPWS_Calendar() {
        // using server time
        $this->loadToday();
        $this->loadRequestDate();
        $this->loadCurrentView();
    }

    function loadCurrentView()
    {
        if (isset($_REQUEST['view'])) {
            $this->current_view = $_REQUEST['view'];
        }
    }

    /**
     * Loads todays unix time and date info 
     */
    function loadToday()
    {
        $atime = PHPWS_Time::getTimeArray();
        $this->today        = &$atime['u'];
        $this->request_date = $this->today;
        $this->month        = &$atime['m'];
        $this->day          = &$atime['d'];
        $this->year         = &$atime['y'];
    }

    /**
     * Loads the date requested by user
     */
    function loadRequestDate()
    {
        $change = FALSE;

        if (isset($_REQUEST['y'])) {
            $this->year = (int)$_REQUEST['y'];
            $change = TRUE;
        }

        if (isset($_REQUEST['m'])) {
            $this->month = (int)$_REQUEST['m'];
            $change = TRUE;
        }

        if (isset($_REQUEST['d'])) {
            $this->day = (int)$_REQUEST['d'];
            $change = TRUE;
        }

        if ($change) {
            $this->request_date = PHPWS_Time::convertServerTime(mktime(0,0,0, $this->month, $this->day, $this->year));
            if ($this->request_date < mktime(0,0,0,1,1,1970)) {
                $this->loadToday();
            } else {
                $this->month = date('m', $this->request_date);
                $this->day   = date('d', $this->request_date);
                $this->year  = date('Y', $this->request_date);
            }
        }
    }

    function loadSchedule($personal=FALSE)
    {

        if ($personal) {
            $this->schedule = & new Calendar_Schedule;
            $db = & new PHPWS_DB('calendar_schedule');
            $db->addWhere('user_id', Current_User::getId());
            $result = $db->loadObject($this->schedule);
            $this->schedule->calendar = & $this;
            return $result;
        } else {
            PHPWS_Core::initModClass('calendar', 'Schedule.php');
            if (isset($_REQUEST['schedule_id'])) {
                $this->schedule = & new Calendar_Schedule($_REQUEST['schedule_id']);
            } else {
                $this->schedule = & new Calendar_Schedule;
            }
            $this->schedule->calendar = & $this;
            return TRUE;
        }
    }

    /**
     * Directs the user (non-admin) functions for calendar
     */
    function user()
    {
        $content = $title = NULL;
        /*
            PHPWS_Core::initModClass('calendar', 'User.php');
            $this->user = & new Calendar_User;
            $this->user->calendar = & $this;
            $this->user->main();
        */

        if (isset($_REQUEST['schedule_id'])) {
            $this->loadSchedule();
            if (!$this->schedule->allowView()) {
                Current_User::disallow();
            }
        }

        if (isset($_REQUEST['uop'])) {
            $command = $_REQUEST['uop'];
        } else {
            $command = 'view';
        }

        switch ($command) {
            
        case 'view':
            $content = $this->view();
            break;
        }

        $template['CONTENT'] = $content;
        $template['TITLE']   = $title;
        $final = PHPWS_Template::process($template, 'calendar', 'user_main.tpl');
        Layout::add($final);
    }


    function view()
    {
        $this->loadView();

        switch ($this->current_view) {
        case 'day':
            $content = $this->view->day();
            break;
            
        case 'month_grid':
            $content = $this->view->month_grid('full');
            break;

        case 'month_list':
            $content = $this->view->month_list();
            break;

        case 'week':
            $content = $this->view->week();
            break;
        }

        return $content;
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
            if ($i < 10) {
                $value = '0' . $i;
            } else {
                $value = &$i;
            }
            $months[$value] = strftime(CALENDAR_MONTH_LISTING, mktime(0,0,0,$i));
        }

        return $months;
    }

    function getDayArray()
    {
        for ($i=1; $i < 32; $i++) {
            if ($i < 10) {
                $value = '0' . $i;
            } else {
                $value = &$i;
            }

            $days[$value] = $i;
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