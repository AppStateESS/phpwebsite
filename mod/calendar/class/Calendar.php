<?php

  /**
   * Main command class for Calendar module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('calendar', 'View.php');

class PHPWS_Calendar {
    var $view = NULL;
    var $today = 0;
    var $month = NULL;

    function PHPWS_Calendar() {
        $this->view = & new Calendar_View;
        $this->view->oCal = & $this;
        // using server time
        $this->today = PHPWS_Time::mkservertime();
    }

    /**
     * Directs the user (non-admin) functions for calendar
     */
    function user()
    {
        if (isset($_REQUEST['view'])) {
            switch ($_REQUEST['view']) {
            case 'full':
                Layout::add($this->view->month_grid('full', $_REQUEST['month'], $_REQUEST['year']));
                break;
            }
        }

    }

    /**
     * Directs the administrative functions for calendar
     */
    function admin()
    {
        
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

    function checkDate($date)
    {
        if ( empty($date) || $date < gmmktime(0,0,0, 1, 1, 1970)) {
            $date = & $this->today;
        }

        return $date;
    }

}

?>