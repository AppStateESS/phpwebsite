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

    /**
     * Current error if exists
     * @var object
     */
    var $_event = null;

    function Calendar_Event($schedule=null, $event_id=0)
    {
        if ($schedule) {
            $this->_schedule = & $schedule;
            if (empty($event_id)) {
                $this->start_time = PHPWS_Time::getUserTime();
                $this->end_time   = PHPWS_Time::getUserTime();
                return;
            } else {
                $this->id = (int)$event_id;
                $this->init();
            }
        }
    }


    function deleteLink()
    {
        if (javascriptEnabled()) {
            $vars['QUESTION'] = _('Are you sure you want to permanently delete this event?');
            $vars['ADDRESS'] = sprintf('index.php?module=calendar&amp;aop=delete_event&amp;sch_id=%s&amp;event_id=%s',
                                       $this->_schedule->id,
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


    function editLink()
    {
        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&amp;js=1&amp;sch_id=%s&amp;aop=edit_event&amp;event_id=%s',
                                       $this->_schedule->id,
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


    /**
     * Creates the edit form for an event
     */
    function form()
    {
        // the form id is linked to the check_date javascript
        $form = & new PHPWS_Form('event_form');
        if (isset($_REQUEST['js'])) {
            $form->addHidden('js', 1);
        }

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_event');
        $form->addHidden('event_id', $this->id);
        $form->addHidden('sch_id', $this->_schedule->id);

        $form->addText('title', $this->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 60);

        $form->addTextArea('summary', $this->summary);
        $form->useEditor('summary');
        $form->setLabel('summary', _('Summary'));

        $form->addText('start_date', $this->getStartTime('%Y/%m/%d'));
        $form->setLabel('start_date', _('Start time'));
        $form->setExtra('start_date', 'onblur="check_start_date()"');

        $form->addText('end_date', $this->getEndTime('%Y/%m/%d'));
        $form->setLabel('end_date', _('End time'));
        $form->setExtra('end_date', 'onblur="check_end_date()" onfocus="check_start_date()"');

        $form->addButton('close', _('Cancel'));
        $form->setExtra('close', 'onclick="window.close()"');

        $this->timeForm('start_time', $this->start_time, $form);
        $this->timeForm('end_time', $this->end_time, $form);

        $form->setExtra('start_time_hour', 'onchange="check_start_date()"');
        $form->setExtra('end_time_hour', 'onchange="check_end_date()"');

        $event_types[1] = 1;
        $event_labels[1] = _('Normal');
        $event_types[2] = 2;
        $event_labels[2] = _('All day');
        $event_types[3] = 3;
        $event_labels[3] = _('Starts at');
        $event_types[4] = 4;
        $event_labels[4] = _('Deadline');

        $form->addRadio('event_type', $event_types);
        $form->setLabel('event_type', $event_labels);
        $form->setExtra('event_type', 'onchange="alter_date(this)"');

        $form->setMatch('event_type', $this->event_type);
        $form->addTplTag('EVENT_TYPE_LABEL', _('Event type'));

        $form->addSubmit(_('Save event'));

        $tpl = $form->getTemplate();

        $js_vars['date_name'] = 'start_date';
        $tpl['START_CAL'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'end_date';
        $tpl['END_CAL'] = javascript('js_calendar', $js_vars);

        if (isset($this->_error)) {
            $tpl['ERROR'] = implode('<br />', $this->_error);
        }

        javascript('modules/calendar/check_date');
        return PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_event.tpl');
    }


    function getEndTime($format='%c')
    {
        return strftime($format, $this->end_time);
    }


    function getStartTime($format='%c')
    {
        return strftime($format, $this->start_time);
    }


    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
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
            $sTime = sprintf(_('Starts at %s.'), strftime(CALENDAR_TIME_LIST_FORMAT, $this->start_time));
            break;

        case 4:
            $sTime = sprintf(_('Deadline at %s.'),  strftime(CALENDAR_TIME_LIST_FORMAT, $this->end_time));
            break;
        }

        return $sTime;
    }

    /**
     * Returns a linkable title (if linked is true)
     */
    function getTitle($linked=true)
    {
        if ($linked) {
            $vars['view'] = 'event';
            $vars['id']   = $this->id;
            if (javascriptEnabled()) {
                $vars['js'] = 1;
                $js['address'] = PHPWS_Text::linkAddress('calendar', $vars);
                $js['label'] = $this->title;
                $js['width'] = '640';
                $js['height'] = '480';

                return javascript('open_window', $js);
            } else {
                return PHPWS_Text::moduleLink($this->title, 'calendar', $vars);
            }

        } else {
            return $this->title;
        }
    }

    function getTpl()
    {
        $tpl['TITLE']   = $this->getTitle();
        $tpl['SUMMARY'] = $this->getSummary();
        $tpl['TIME']    = $this->getTime();
        if ( ($this->_schedule->public && Current_User::allow('calendar', 'edit_public', $this->_schedule->id)) ||
             (!$this->_schedule->public && Current_User::allow('calendar', 'edit_private', $this->_schedule->id))
             ) {
            $link[] = $this->editLink();
            $link[] = $this->deleteLink();
            $tpl['LINKS'] = implode(' | ', $link);
        }

        

        return $tpl;
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

    function post()
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

        $start_time_hour   = &$_POST['start_time_hour'];
        $start_time_minute = &$_POST['start_time_minute'];
        $end_time_hour     = &$_POST['end_time_hour'];
        $end_time_minute   = &$_POST['end_time_minute'];

        switch ($_POST['event_type']) {
        case '1':
            $startTime = mktime($start_time_hour, $start_time_minute, 0,
                                $start_date_array[1], $start_date_array[2], $start_date_array[0]);
            $endTime   = mktime($end_time_hour, $end_time_minute, 0,
                                $end_date_array[1], $end_date_array[2], $end_date_array[0]);
            if ($startTime >= $endTime) {
                $errors[] = _('The end time must be after the start time.');
            }

            /*
            if (isset($_POST['block'])) {
                $this->block = 1;
            } else {
                $this->block = 0;
            }
            */
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

        if (isset($_POST['sch_id'])) {
            $this->_sch_id = (int)$_POST['sch_id'];
        }

        $this->event_type = (int)$_POST['event_type'];

        if (isset($errors)) {
            $this->_error = &$errors;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function save()
    {
        $table = $this->_schedule->getEventTable();

        if (!PHPWS_DB::isTable($table)) {
            return PHPWS_Error::get();
        }
        
        
        $db = & new PHPWS_DB($table);
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return TRUE;
        }
    }

    function saveKey()
    {

    }


    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function timeForm($name, $match, &$form)
    {
        static $hours = NULL;
        static $minutes = NULL;

        if (empty($hours)) {
            for ($i = 0; $i < 24; $i++) {
                $hours[$i] = strftime(CALENDAR_TIME_FORM_FORMAT, mktime($i));
            }
        }

        $minute_match = (int)strftime('%M', $match);
        $minute_match -= $minute_match % CALENDAR_TIME_MINUTE_INC;

        $form->addSelect($name . '_hour', $hours);
        $form->setMatch($name . '_hour', (int)strftime('%H', $match));

        if (empty($minutes)) {
            for ($i = 0; $i < 60; $i += CALENDAR_TIME_MINUTE_INC) {
                $minutes[$i] = strftime('%M', mktime(1,$i));
            }
        }
        $form->addSelect($name . '_minute', $minutes);
        $form->setMatch($name . '_minute', $minute_match);
    }


}


?>