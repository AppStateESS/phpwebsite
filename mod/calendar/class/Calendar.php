<?php

  /**
   * Main command class for Calendar module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('calendar');
PHPWS_Core::requireInc('calendar', 'error_defines.php');

define('MINI_CAL_NO_SHOW', 1);
define('MINI_CAL_SHOW_FRONT', 2);
define('MINI_CAL_SHOW_ALWAYS', 3);


class PHPWS_Calendar {
    /**
     * unix timestamp of today
     * @var integer
     */
    var $today = 0;

    /**
     * unix timestamp according to passed variables
     * @var integer
     */
    var $current_date = 0;

    /**
     * month number based on current_date
     * @var integer
     */
    var $int_month = null;

    /**
     * day number based on current_date
     * @var integer
     */
    var $int_day   = null;

    /**
     * year number based on current_date
     * @var integer
     */
    var $int_year  = null;

    /**
     * Contains the administrative object
     */
    var $admin = null;

    /**
     * Contains the user object
     */
    var $user = null;

    var $schedule = null;

    /**
     * Array of events loaded into the object
     * @var array
     */
    var $event_list = null;

    var $sorted_list = null;


    function PHPWS_Calendar()
    {
        $this->loadToday();
        $this->loadRequestDate();
        $this->loadSchedule();
        
    }

    /**
     * Directs the administrative functions for calendar
     */
    function admin()
    {
        PHPWS_Core::initModClass('calendar', 'Admin.php');
        $this->admin = & new Calendar_Admin;
        $this->admin->calendar = & $this;
        $this->admin->main();
    }

    function &getDay()
    {
        require_once 'Calendar/Day.php';
        $oDay = & new Calendar_Day($this->int_year, $this->int_month, $this->int_day);
        $oDay->build();
        return $oDay;
    }


    function getEvents($start_search=null, $end_search=null, $schedules=null) {

        PHPWS_Core::initModClass('calendar', 'Event.php');
        if (!isset($start_search)) {
            $start_search = mktime(0,0,0,1,1,1970);
        } 

        if (!isset($end_search)) {
            // if this line is a problem, you need to upgrade
            $end_search = mktime(0,0,0,1,1,2050);
        }

        $db = & new PHPWS_DB($this->schedule->getEventTable());

        $db->addWhere('start_time', $start_search, '>=', null,  'start');
        $db->addWhere('start_time', $end_search,   '<',  'AND', 'start');

        $db->addWhere('end_time',   $end_search,   '<=', null,  'end');
        $db->addWhere('end_time',   $start_search, '>',  'AND', 'end');

        $db->addWhere('start_time', $start_search, '<',  null,  'middle');
        $db->addWhere('end_time',   $end_search,   '>',  'AND', 'middle');

        $db->setGroupConj('end', 'OR');
        $db->setGroupConj('middle', 'OR');

        $db->addOrder('start_time');
        $db->addOrder('end_time desc');
        $db->setIndexBy('id');
        
        $result = $db->getObjects('Calendar_Event', $this->schedule);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return null;
        }

        return $result;
    }


        
    function &getMonth()
    {
        require_once 'Calendar/Month/Weekdays.php';
        $oMonth = & new Calendar_Month_Weekdays($this->int_year, $this->int_month, PHPWS_Settings::get('calendar', 'starting_day'));
        return $oMonth;
    }

    /**
     * Returns a list of schedules according to the user's permissions
     */
    function getScheduleList($mode='object')
    {
        $db = & new PHPWS_DB('calendar_schedule');
        Key::restrictView($db);
        $db->addOrder('title');
        
        switch ($mode) {
        case 'object':
            return $db->getObjects('Calendar_Schedule');
            break;

            
        case 'brief':
            $db->addColumn('id');
            $db->addColumn('title');
            $db->setIndexBy('id');
            return $db->select('col');
            break;
        }
        

    }


    function &getWeek()
    {
        require_once 'Calendar/Week.php';

        $oWeek = & new Calendar_Week($this->int_year, $this->int_month, $this->int_day, CALENDAR_START_DAY);
        $oWeek->build();
        return $oWeek;
    }


    function isJS()
    {
        return isset($_REQUEST['js']);
    }


    function loadDefaultSchedule()
    {
        $sch_id = PHPWS_Settings::get('calendar', 'public_schedule');
        if ($sch_id) {
            $this->schedule = & new Calendar_Schedule((int)$sch_id);
        }
    }


    function loadEventList($start_search=null, $end_search=null)
    {
        $result = $this->getEvents($start_search, $end_search, $this->schedule->id);
        $this->event_list = & $result;
        $this->sortEvents();
        return true;
    }


    /**
     * Loads the date requested by user
     */
    function loadRequestDate()
    {
        $change = false;

        if (!empty($_REQUEST['date'])) {
            $this->int_year  =    (int)date('Y', (int)$_REQUEST['date']);
            $this->int_month =    (int)date('m', (int)$_REQUEST['date']);
            $this->int_day   =    (int)date('j', (int)$_REQUEST['date']);
            $this->current_date = (int)$_REQUEST['date'];
            return;
        } else {
            if (!empty($_REQUEST['y'])) {
                $this->int_year = (int)$_REQUEST['y'];
                $change = true;
            } elseif (!empty($_REQUEST['year'])) {
                $this->int_year = (int)$_REQUEST['year'];
                $change = true;
            }

            if (!empty($_REQUEST['m'])) {
                $this->int_month = (int)$_REQUEST['m'];
                $change = true;
            } elseif (!empty($_REQUEST['month'])) {
                $this->int_month = (int)$_REQUEST['month'];
                $change = true;
            }


            if (!empty($_REQUEST['d'])) {
                $this->int_day = (int)$_REQUEST['d'];
                $change = true;
            } elseif (!empty($_REQUEST['day'])) {
                $this->int_day = (int)$_REQUEST['day'];
                $change = true;
            }
        }


        if ($change) {
            $this->current_date = mktime(0,0,0, $this->int_month, $this->int_day, $this->int_year);

            if ($this->current_date < mktime(0,0,0,1,1,1970)) {
                $this->loadToday();
            } else {
                $this->int_month = (int)date('m', $this->current_date);
                $this->int_day   = (int)date('d', $this->current_date);
                $this->int_year  = (int)date('Y', $this->current_date);
            }
        }
    }


    /**
     * Loads either the requested schedule, the default public schedule
     * (if use_default is true) or an empty schedule object
     */
    function &loadSchedule()
    {
        PHPWS_Core::initModClass('calendar', 'Schedule.php');

        if (!empty($_REQUEST['sch_id'])) {
            $this->schedule = & new Calendar_Schedule((int)$_REQUEST['sch_id']);
        }

        if (empty($this->schedule)) {
            $this->schedule = & new Calendar_Schedule;
        }
    }

    /**
     * Loads todays unix time and date info 
     */
    function loadToday()
    {
        $atime = PHPWS_Time::getTimeArray();
        $this->today        = &$atime['u'];
        $this->current_date = $this->today;
        $this->int_month        = &$atime['m'];
        $this->int_day          = &$atime['d'];
        $this->int_year         = &$atime['y'];
    }

    function loadUser()
    {
        PHPWS_Core::initModClass('calendar', 'User.php');
        $this->user = & new Calendar_User;
        $this->user->calendar = & $this;
    }
    

    function sortEvents()
    {
        if (empty($this->event_list)) {
            return;
        }

        foreach ($this->event_list as $key => $event) {
            $syear  = (int)date('Y', $event->start_time);
            $smonth = (int)date('m', $event->start_time);
            $sday   = (int)date('d', $event->start_time);
            $shour  = (int)date('H', $event->start_time);
            $sdate  = (int)date('Ymd', $event->start_time);
            $edate  = (int)date('Ymd', $event->end_time);

            $this->sorted_list[$syear]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['days'][$sday]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['days'][$sday]['hours'][$shour]['events'][$key] = & $this->event_list[$key];

            if ($sdate != $edate) {
                for ($i = $event->start_time + 86400; $i <= $event->end_time; $i += 86400) {
                    $copy_month = (int)date('m', $i);
                    $copy_day   = (int)date('d', $i);
                    $copy_year  = (int)date('Y', $i);

                    $this->sorted_list[$copy_year]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['days'][$copy_day]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['days'][$copy_day]['hours'][$shour]['events'][$key] = & $this->event_list[$key];
                }
            }

        }
    }

    function user()
    {
        $this->loadUser();
        if (!$this->schedule->id) {
            $this->loadDefaultSchedule();
            if (!$this->schedule->id) {
                $this->schedule->title = _('General');
            }
        }

        $this->user->main();
    }
}

?>