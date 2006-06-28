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
    var $id           = 0;
    var $key_id       = 0;
    var $title        = NULL;
    var $summary      = NULL;
    var $event_type   = 1;   // 1 normal, 2 all day, 3 starts at, 4 deadline
    var $start_time   = 0;
    var $end_time     = 0;
    var $post_start   = 0;   // date to show on calendar, 0 means immediately
    var $post_end     = 0;   // date to remove from calendar, 0 means never
    var $public       = 0;   // 1 means event is viewable by public
    var $block        = 0;   // 1 means the event displays as taking up time slots
    var $sticky       = 0;   // 1 means this event will always show in a shortened listing
    var $_schedule_id = 0;   // This current schedule using this event
    var $_error       = NULL;


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

    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
    }

    function getStartTime($format='%c')
    {
        return strftime($format, $this->start_time);
    }

    function getEndTime($format='%c')
    {
        return strftime($format, $this->end_time);
    }

    /**
     * Returns a formated time for printing
     */
    function getTime()
    {
        switch ($this->event_type) {
        case 1:
            $sTime = sprintf('%s - %s', 
                             strftime(CALENDAR_TIME_LIST_FORMAT, $this->start_time),
                             strftime(CALENDAR_TIME_LIST_FORMAT, $this->end_time)
                             );
            break;

        case 2:
            $sTime = _('All day event');
            break;

        case 3:
            $sTime = sprintf(_('Event starts at %s.'), strftime(CALENDAR_TIME_LIST_FORMAT, $this->start_time));
            break;

        case 4:
            $sTime = sprintf(_('Event deadline at %s.'),  strftime(CALENDAR_TIME_LIST_FORMAT, $this->end_time));
            break;
        }

        return $sTime;
    }


    function getTpl()
    {
        $tpl['TITLE']   = $this->title;
        $tpl['SUMMARY'] = $this->getSummary();
        $tpl['TIME']    = $this->getTime();
        return $tpl;
    }

    function save()
    {
        $db = & new PHPWS_DB('calendar_events');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        } elseif ($this->_schedule_id) {
            $db->reset();
            $db->setTable('calendar_schedule_to_event');
            $db->addWhere('schedule_id', $this->_schedule_id);
            $db->addWhere('event_id', $this->id);
            $result = $db->select('one');
            if (PEAR::isError($result)) {
                return $result;
            } elseif (empty($result)) {
                $db->reset();
                $db->addValue('schedule_id', $this->_schedule_id);
                $db->addValue('event_id', $this->id);
                return $db->insert();
            }
            return TRUE;
        } else {
            return TRUE;
        }
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
            //defaulting to 1 for testing purposes
            if (1 || isset($_POST['block'])) {
                $this->block = 1;
            } else {
                $this->block = 0;
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

        if (isset($_POST['schedule_id'])) {
            $this->_schedule_id = (int)$_POST['schedule_id'];
        }

        $this->event_type = (int)$_POST['event_type'];

        if (isset($errors)) {
            $this->_error = &$errors;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function editLink()
    {
        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&aop=edit_event_js&event_id=%s',
                                       $this->id);
            $vars['link_title'] = $vars['label'] = _('Edit');
            $vars['width'] = CALENDAR_EVENT_WIDTH;
            $vars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Edit'), 'calendar',
                                          array('aop'         => 'create_event',
                                                'schedule_id' => $this->id,
                                                'date'        => $default_date)
                                          );
        }
    }

    function removeLink($schedule_id)
    {
        if (javascriptEnabled()) {
            $vars['QUESTION'] = _('Are you sure you want to remove this event from this calendars?');
            $vars['ADDRESS'] = sprintf('index.php?module=calendar&aop=remove_event&schedule_id=%s&event_id=%s',
                                       $schedule_id, $this->id);
            $vars['LINK']    = _('Remove');
            return javascript('confirm', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Remove'), 'calendar',
                                          array('aop'         => 'remove_event',
                                                'schedule_id' => $schedule_id,
                                                'event_id'    => $this->id
                                                )
                                          );
        }

    }

    function deleteLink()
    {
        if (javascriptEnabled()) {
            $vars['QUESTION'] = _('Are you sure you want to permanently delete this event from all calendars?');
            $vars['ADDRESS'] = sprintf('index.php?module=calendar&aop=delete_event&event_id=%s',
                                       $this->id);
            $vars['LINK']    = _('Delete');
            return javascript('confirm', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Delete'), 'calendar',
                                          array('aop'         => 'delete_event',
                                                'event_id'    => $this->id
                                                )
                                          );
        }

    }

}

?>