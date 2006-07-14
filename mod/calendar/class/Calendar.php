<?php

  /**
   * Main command class for Calendar module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('calendar');

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

    function PHPWS_Calendar()
    {
        $this->loadToday();
        $this->loadRequestDate();
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


    function isJS()
    {
        return isset($_REQUEST['js']);
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


    /**
     * Loads the date requested by user
     */
    function loadRequestDate()
    {
        $change = FALSE;

        if (isset($_REQUEST['y'])) {
            $this->int_year = (int)$_REQUEST['y'];
            $change = TRUE;
        } elseif (isset($_REQUEST['year'])) {
            $this->int_year = (int)$_REQUEST['year'];
            $change = TRUE;
        }

        if (isset($_REQUEST['m'])) {
            $this->int_month = (int)$_REQUEST['m'];
            $change = TRUE;
        } elseif (isset($_REQUEST['month'])) {
            $this->int_month = (int)$_REQUEST['month'];
            $change = TRUE;
        }


        if (isset($_REQUEST['d'])) {
            $this->int_day = (int)$_REQUEST['d'];
            $change = TRUE;
        } elseif (isset($_REQUEST['day'])) {
            $this->int_day = (int)$_REQUEST['day'];
            $change = TRUE;
        }


        if ($change) {
            $this->current_date = PHPWS_Time::convertServerTime(mktime(0,0,0, $this->int_month, $this->int_day, $this->int_year));
            if ($this->current_date < mktime(0,0,0,1,1,1970)) {
                $this->loadToday();
            } else {
                $this->int_month = (int)date('m', $this->current_date);
                $this->int_day   = (int)date('d', $this->current_date);
                $this->int_year  = (int)date('Y', $this->current_date);
            }
        }
    }
}

?>