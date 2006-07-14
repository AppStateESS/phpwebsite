<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('CALENDAR_EVENT_TYPE_NORMAL',  1);
define('CALENDAR_EVENT_TYPE_ALL_DAY', 2);
define('CALENDAR_EVENT_TYPE_STARTS',  3);
define('CALENDAR_EVENT_TYPE_ENDS'  ,  4);

class Calendar_Event {
    /**
     * @var integer
     */
    var $id      = 0;

    /**
     * @var string
     */
    var $title   = null;
    
    /**
     * @var string
     */
    var $summary = null;

    /**
     * Type of event (normal, all day, start time only, end time only)
     * @var integer
     */
    var $event_type = CALENDAR_EVENT_TYPE_NORMAL;

    /**
     * Start time of event
     * @var integer
     */
    var $start_time = 0;


    /**
     * End time of event
     * @var integer
     */
    var $end_time = 0;


    /**
     * 
     */
    var $active = true;

    /**
     * pointer to the parent schedule object
     * @var object
     */
    var $_schedule = null;

    function Calendar_Event($schedule, $event_id=0)
    {
        $this->_schedule = & $schedule;
        if (empty($event_id)) {
            return;
        } else {
            $this->id = (int)$event_id;
            $this->init();
        }

    }

    function init()
    {
        $table = $this->_schedule->getEventTable();
        
        if (empty($table)) {
            // error here
            return;
        }

        $db = & new PHPWS_DB($table);
        $db->loadObject($this);
    }

}


?>