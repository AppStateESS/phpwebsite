<?php

  /**
   * Main command class for Calendar module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('calendar', 'View.php');

class Calendar {
    var $view = NULL;
    var $today = 0;
    var $month = NULL;

    function Calendar() {
        $this->view = & new Calendar_View;
        $this->view->oCal = & $this;
        $this->today = gmmktime();
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

    function getMonth($date)
    {

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