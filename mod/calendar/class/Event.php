<?php

  /**
   * Event object
   *
   *  start_time = 0, end_time > 0 Deadline event
   *  start_time > 0, end_time = 0 Event starts
   *  start_time = 00:00, end_time = 23:59 all day
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_Event {
    var $id         = 0;
    var $key_id     = 0;
    var $title      = NULL;
    var $summary    = NULL;
    var $event_type = 1;   // 1 normal, 2 all day, 3 starts at, 4 deadline
    var $start_time = 0;
    var $end_time   = 0;
    var $post_start = 0;   // date to show on calendar, 0 means immediately
    var $post_end   = 0;   // date to remove from calendar, 0 means never
    var $public     = 0;   // 1 means event is viewable by public
    var $sticky     = 0;   // 1 means this event will always show in a shortened listing
    var $_error     = NULL;


    function Calendar_Event($id=NULL)
    {
        if (!$id) {
            $this->start_time = mktime();
            $this->end_time = mktime();
            return;
        }

        $this->setId($id);
        $this->init();
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function init()
    {
        $db = & new PHPWS_DB('calendar_events');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }
        return TRUE;
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    function getStartTime($format='%c')
    {
        return strftime($format, $this->start_time);
    }

    function getEndTime($format='%c')
    {
        return strftime($format, $this->end_time);
    }

    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
    }

    function save()
    {
        $db = & new PHPWS_DB('calendar_events');
        $result = $db->saveObject($this);
        return $result;
    }

    function saveKey()
    {

    }

    function postEvent()
    {
        if (empty($_POST['title'])) {
            $errors[] = _('You must give your event a title.');
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setSummary($_POST['summary']);

        $start_date = & $_POST['start_date'];
        $end_date = & $_POST['end_date'];

        $start_date_array = explode('/', $start_date);
        $end_date_array = explode('/', $start_date);

        $start_time_hour = &$_POST['start_time_hour'];
        $start_time_minute = &$_POST['start_time_minute'];
        $end_time_hour = &$_POST['end_time_hour'];
        $end_time_minute = &$_POST['end_time_minute'];

        switch ($_POST['event_type']) {
        case '1':
            $startTime = mktime($start_time_hour, $start_time_minute, 0,
                                $start_date_array[1], $start_date_array[2], $start_date_array[0]);
            $endTime   = mktime($end_time_hour, $end_time_minute, 0,
                                $end_date_array[1], $end_date_array[2], $end_date_array[0]);
            if ($startTime >= $endTime) {
                $errors[] = _('The end time must be after the start time.');
            }
            break;

        case '2':
            $startTime = mktime(0, 0, 0,
                                $start_date_array[1], $start_date_array[2], $start_date_array[0]);
            $endTime   = mktime(23, 59, 59,
                                $end_date_array[1], $end_date_array[2], $end_date_array[0]);
            break;

        case '3':
            $startTime = mktime($start_time_hour, $start_time_minute, 0,
                                $start_date_array[1], $start_date_array[2], $start_date_array[0]);
            $endTime   = mktime(23, 59, 59,
                                $end_date_array[1], $end_date_array[2], $end_date_array[0]);
            break;

        case '4':
            $startTime = mktime(0, 0, 0,
                                $start_date_array[1], $start_date_array[2], $start_date_array[0]);
            $endTime   = mktime($end_time_hour, $end_time_minute, 0,
                                $end_date_array[1], $end_date_array[2], $end_date_array[0]);
            break;

        }

        $this->start_time = $startTime;
        $this->end_time   = $endTime;

        $this->event_type = (int)$_POST['event_type'];

        if (isset($errors)) {
            $this->_error = &$errors;
            return FALSE;
        } else {
            return TRUE;
        }
    }


}

?>