<?php

/**
 * Main command class for Calendar module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
\phpws\PHPWS_Core::requireConfig('calendar');
\phpws\PHPWS_Core::requireInc('calendar', 'error_defines.php');

define('MINI_CAL_NO_SHOW', 0);
define('MINI_CAL_SHOW_FRONT', 1);
define('MINI_CAL_SHOW_ALWAYS', 2);

class PHPWS_Calendar {

    /**
     * unix timestamp of today
     * @var integer
     */
    public $today = 0;

    /**
     * unix timestamp according to passed variables
     * @var integer
     */
    public $current_date = 0;

    /**
     * month number based on current_date
     * @var integer
     */
    public $int_month = null;

    /**
     * day number based on current_date
     * @var integer
     */
    public $int_day = null;

    /**
     * year number based on current_date
     * @var integer
     */
    public $int_year = null;

    /**
     * Contains the administrative object
     */
    public $admin = null;

    /**
     * Contains the user object
     */
    public $user = null;
    public $schedule = null;

    /**
     * Array of events loaded into the object
     * @var array
     */
    public $event_list = null;
    public $sorted_list = null;

    public function __construct()
    {
        $this->loadToday();
        $this->loadRequestDate();
        $this->loadSchedule();
    }

    /**
     * Directs the administrative functions for calendar
     */
    public function admin()
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Admin.php');
        $this->admin = new Calendar_Admin;
        $this->admin->calendar = & $this;
        $this->admin->main();
    }

    public function getDay()
    {
        require_once 'Calendar/Day.php';
        $oDay = new Calendar_Day($this->int_year, $this->int_month,
                $this->int_day);
        $oDay->build();
        return $oDay;
    }

    public function getEvents($start_search = null, $end_search = null)
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');
        if (!isset($start_search)) {
            $start_search = mktime(0, 0, 0, 1, 1, 1970);
        }

        if (!isset($end_search)) {
            // if this line is a problem, you need to upgrade
            $end_search = mktime(0, 0, 0, 1, 1, 2050);
        }

        return $this->schedule->getEvents($start_search, $end_search);
    }

    public function getMonth($month = 0, $year = 0)
    {
        if (!$month) {
            $month = &$this->int_month;
        }

        if (!$year) {
            $year = &$this->int_year;
        }

        $start_day = (int) PHPWS_Settings::get('calendar', 'starting_day');
        require_once 'Calendar/Month/Weekdays.php';
        $oMonth = new Calendar_Month_Weekdays($year, $month, $start_day);
        return $oMonth;
    }

    /**
     * Returns a list of schedules according to the user's permissions
     */
    public function getScheduleList($mode = 'object')
    {
        $db = new PHPWS_DB('calendar_schedule');
        \Canopy\Key::restrictView($db);
        $user_id = Current_User::getId();

        if ($user_id) {
            // this should always be true, adding just to create another where group
            $db->addWhere('id', 0, '>', 'and', 'user_cal0');
            $db->addWhere('user_id', $user_id, '=', 'and', 'user_cal1');
            $db->addWhere('public', 0, '=', 'and', 'user_cal1');
            $db->addWhere('public', 1, '=', 'or', 'user_cal2');
            $db->setGroupConj('user_cal1', 'and');
            $db->setGroupConj('user_cal2', 'or');
            $db->groupIn('user_cal1', 'user_cal0');
            $db->groupIn('user_cal2', 'user_cal0');
        } else {
            $db->addWhere('public', 1);
        }

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

    public function getWeek()
    {
        require_once 'Calendar/Week.php';
        $start_day = (int) PHPWS_Settings::get('calendar', 'starting_day');
        $oWeek = new Calendar_Week($this->int_year, $this->int_month,
                $this->int_day, $start_day);
        $oWeek->build();
        return $oWeek;
    }

    public static function isJS()
    {
        return isset($_REQUEST['js']);
    }

    public function loadDefaultSchedule()
    {
        $sch_id = PHPWS_Settings::get('calendar', 'public_schedule');

        if ($sch_id > 0) {
            $this->schedule = new Calendar_Schedule((int) $sch_id);
        } elseif ($sch_id == -1) {
            $this->schedule = new Calendar_Schedule;
        } else {
            $db = new PHPWS_DB('calendar_schedule');
            $db->addColumn('id');
            $db->addWhere('public', 1);
            $db->setLimit(1);
            $id = $db->select('one');

            if (PHPWS_Error::isError($id)) {
                PHPWS_Error::log($id);
                return;
            }

            if (empty($id)) {
                $id = -1;
            }
            PHPWS_Settings::set('calendar', 'public_schedule', $id);
            PHPWS_Settings::save('calendar');
        }
    }

    public function loadEventList($start_search = null, $end_search = null)
    {
        $result = $this->getEvents($start_search, $end_search);
        $this->event_list = & $result;
        $this->sortEvents();
        return true;
    }

    /**
     * Loads the date requested by user
     */
    public function loadRequestDate()
    {
        $change = false;

        if (!empty($_REQUEST['date'])) {
            $this->current_date = (int) $_REQUEST['date'];
            $this->int_year = (int) date('Y', $this->current_date);
            $this->int_month = (int) date('m', $this->current_date);
            $this->int_day = (int) date('j', $this->current_date);
            return;
        } elseif (!empty($_REQUEST['jdate'])) {
            $this->current_date = (int) strtotime($_REQUEST['jdate']);
            $this->int_year = (int) date('Y', $this->current_date);
            $this->int_month = (int) date('m', $this->current_date);
            $this->int_day = (int) date('j', $this->current_date);
            return;
        } else {
            if (!empty($_REQUEST['y'])) {
                $this->int_year = (int) $_REQUEST['y'];
                $change = true;
            } elseif (!empty($_REQUEST['year'])) {
                $this->int_year = (int) $_REQUEST['year'];
                $change = true;
            }

            if (!empty($_REQUEST['m'])) {
                $this->int_month = (int) $_REQUEST['m'];
                $change = true;
            } elseif (!empty($_REQUEST['month'])) {
                $this->int_month = (int) $_REQUEST['month'];
                $change = true;
            }

            if (!empty($_REQUEST['d'])) {
                $this->int_day = (int) $_REQUEST['d'];
                $change = true;
            } elseif (!empty($_REQUEST['day'])) {
                $this->int_day = (int) $_REQUEST['day'];
                $change = true;
            }
        }

        if ($change) {
            $this->current_date = mktime(0, 0, 0, $this->int_month,
                    $this->int_day, $this->int_year);

            if ($this->current_date < mktime(0, 0, 0, 1, 1, 1970)) {
                $this->loadToday();
            } else {
                $this->int_month = (int) date('m', $this->current_date);
                $this->int_day = (int) date('d', $this->current_date);
                $this->int_year = (int) date('Y', $this->current_date);
            }
        }
    }

    /**
     * Loads either the requested schedule, the default public schedule
     * (if use_default is true) or an empty schedule object
     */
    public function loadSchedule()
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Schedule.php');

        if (!empty($_REQUEST['sch_id'])) {
            $this->schedule = new Calendar_Schedule($_REQUEST['sch_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->schedule = new Calendar_Schedule($_REQUEST['id']);
        }

        if (empty($this->schedule) || !$this->schedule->id) {
            $this->schedule = new Calendar_Schedule;
        } else {
            $_SESSION['Current_Schedule'] = $this->schedule->id;
        }
    }

    /**
     * Loads todays unix time and date info
     */
    public function loadToday()
    {
        $atime = PHPWS_Time::getTimeArray();
        $this->today = &$atime['u'];
        $this->current_date = $this->today;
        $this->int_month = &$atime['m'];
        $this->int_day = &$atime['d'];
        $this->int_year = &$atime['y'];
    }

    public function loadUser()
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'User.php');
        $this->user = new Calendar_User;
        $this->user->calendar = & $this;
    }

    public function sortEvents()
    {
        if (empty($this->event_list)) {
            return;
        }

        foreach ($this->event_list as $key => $event) {
            $syear = (int) date('Y', $event->start_time);
            $smonth = (int) date('m', $event->start_time);
            $sday = (int) date('d', $event->start_time);
            $shour = (int) date('H', $event->start_time);
            $sdate = (int) date('Ymd', $event->start_time);
            $edate = (int) date('Ymd', $event->end_time);

            $this->sorted_list[$syear]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['days'][$sday]['events'][$key] = & $this->event_list[$key];
            $this->sorted_list[$syear]['months'][$smonth]['days'][$sday]['hours'][$shour]['events'][$key] = & $this->event_list[$key];

            if ($sdate != $edate) {
                for ($i = $event->start_time + 86400; $i <= $event->end_time; $i += 86400) {
                    $copy_month = (int) date('m', $i);
                    $copy_day = (int) date('d', $i);
                    $copy_year = (int) date('Y', $i);


                    $this->sorted_list[$copy_year]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['days'][$copy_day]['events'][$key] = & $this->event_list[$key];
                    $this->sorted_list[$copy_year]['months'][$copy_month]['days'][$copy_day]['hours'][$shour]['events'][$key] = & $this->event_list[$key];
                }
            }
        }
    }

    public function user()
    {
        $this->loadUser();
        if (!$this->schedule->id) {
            $this->loadDefaultSchedule();
            if (!$this->schedule->id) {
                $this->schedule->title = 'General';
            }
        }

        $this->user->main();
    }

}

