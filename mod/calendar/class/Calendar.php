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
        
    }

    /**
     * Directs the administrative functions for calendar
     */
    function admin()
    {
        
    }

    function &getMonth($date)
    {
        require_once 'Calendar/Month/Weekdays.php';
        $month = & new Calendar_Month_Weekdays(date('Y', $date), date('m', $date));
        $month->build();
        return $month;
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