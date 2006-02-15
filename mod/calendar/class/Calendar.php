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
            $this->loadView();
            if (isset($_REQUEST['view'])) {
                $mode = $_REQUEST['view'];
            } else {
                $mode = DEFAULT_CALENDAR_VIEW;
            }
            $title = $this->viewLinks($mode);
            $content = $this->view($mode);
            break;
        }

        $template['CONTENT'] = $content;
        $template['TITLE']   = $title;
        $final = PHPWS_Template::process($template, 'calendar', 'user_main.tpl');
        Layout::add($final);
    }

    function viewLinks($current_view)
    {
        $vars['schedule_id'] = $this->schedule->id;
        if (isset($_REQUEST['m'])) {
            $vars['m'] = &$_REQUEST['m'];
        }

        if (isset($_REQUEST['d'])) {
            $vars['d'] = &$_REQUEST['d'];
        }

        if (isset($_REQUEST['y'])) {
            $vars['y'] = &$_REQUEST['y'];
        }

        if ($current_view == 'month_list') {
            $links[] = _('Month list');
        } else {
            $vars['view'] = 'month_list';
            $links[] = PHPWS_Text::moduleLink(_('Month list'), 'calendar', $vars);
        }

        if ($current_view == 'month_grid') {
            $links[] = _('Month list');
        } else {
            $vars['view'] = 'month_grid';
            $links[] = PHPWS_Text::moduleLink(_('Month grid'), 'calendar', $vars);
        }

        if ($current_view == 'week') {
            $links[] = _('Week');
        } else {
            $vars['view'] = 'week';
            $links[] = PHPWS_Text::moduleLink(_('Week'), 'calendar', $vars);
        }

        if ($current_view == 'day') {
            $links[] = _('Day');
        } else {
            $vars['view'] = 'day';
            $links[] = PHPWS_Text::moduleLink(_('Day'), 'calendar', $vars);
        }
        
        return implode(' | ', $links);

    }

    function view($mode=NULL)
    {
        $this->loadView();

        if (!isset($mode)) {
            $mode = 'day';
        }

        switch ($mode) {
        case 'day':
            return $this->view->day();
            break;
            
        case 'month_grid':
            return $this->view->month_grid('full');
            break;
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