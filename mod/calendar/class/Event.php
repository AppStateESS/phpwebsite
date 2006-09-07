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
     * @var integer
     */
    var $key_id  = 0;

    /**
     * Summary of event
     * @var string
     */
    var $summary  = null;

    /**
     * location of event
     * @var string
     */
    var $location = null;

    /**
     * link to location
     */
    var $loc_link = null;

    /**
     * @var string
     */
    var $description = null;

    /**
     * If true (1) then this is an all day event
     * @var int
     */
    var $all_day     = 0;

   
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
     *  If true (1), then display as "Busy"
     * @var integer
     */
    var $show_busy   = 0;

    /**
     * pointer to the parent schedule object
     * @var object
     */
    var $_schedule = null;

    /**
     * Current error if exists
     * @var object
     */
    var $_error = null;

    /**
     * Key object for this schedule
     * @var object
     */
    var $_key           = null;


    function Calendar_Event($schedule=null, $event_id=0)
    {
        if ($schedule) {
            $this->_schedule = & $schedule;
            if (empty($event_id)) {
                if (!$this->_schedule->public) {
                    $this->show_busy = 1;
                }
                $this->start_time = PHPWS_Time::getUserTime();
                $this->end_time   = PHPWS_Time::getUserTime();
                return;
            } else {
                $this->id = (int)$event_id;
                $result = $this->init();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->id = 0;
                } elseif (!$result) {
                    $this->id = 0;
                }
            }
        }
    }

    /**
     * Returns true if the event time span is over one day in length
     */
    function dayDiff()
    {
        if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
            return true;
        } else {
            return false;
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


    function editLink($full=false)
    {
        $linkvar['aop']      = 'edit_event';
        $linkvar['sch_id']   = $this->_schedule->id;
        $linkvar['event_id'] = $this->id;

        if ($full) {
            $link_label = _('Edit event');
        } else {
            $link_label = _('Edit');
        }

        if (javascriptEnabled()) {
            $linkvar['js'] = 1;
            $jsvars['address'] = PHPWS_Text::linkAddress('calendar', $linkvar);
            $jsvars['link_title'] = $jsvars['label'] = $link_label;
            $jsvars['width'] = CALENDAR_EVENT_WIDTH;
            $jsvars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $jsvars);
        } else {
            return PHPWS_Text::moduleLink($link_label, 'calendar', $linkvar);
        }
    }


    function flagKey()
    {
        if (!isset($this->_key)) {
            $this->_key = & new Key($this->key_id);
        }
        $this->_key->flag();
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

        $form->addText('summary', $this->summary);
        $form->setLabel('summary', _('Summary'));
        $form->setSize('summary', 60);

        $form->addText('location', $this->location);
        $form->setLabel('location', _('Location'));
        $form->setSize('location', 60);

        $form->addText('loc_link', $this->loc_link);
        $form->setLabel('loc_link', _('Location link'));
        $form->setSize('loc_link', 60);

        $form->addTextArea('description', $this->description);
        $form->useEditor('description');
        $form->setLabel('description', _('Description'));

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

        $form->addCheck('all_day', 1);
        $form->setMatch('all_day', $this->all_day);
        $form->setLabel('all_day', _('All day event'));
        $form->setExtra('all_day', 'onchange="alter_date(this)"');

        $form->addCheck('show_busy', 1);
        $form->setMatch('show_busy', $this->show_busy);
        $form->setLabel('show_busy', _('Show busy'));

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

    function getEndTime($format='%c', $mode=null)
    {
        $time = &$this->end_time;

        if ($mode == 'user') {
            $time = PHPWS_Time::getUserTime($time);
        } elseif ($mode == 'server') {
            $time = PHPWS_Time::getUserTime($time);
        }

        return strftime($format, $time);
    }


    function &getKey()
    {
        if (!$this->_key) {
            $this->_key = & new Key($this->key_id);
        }

        return $this->_key;
    }

    function getStartTime($format='%c', $mode=null)
    {
        $time = &$this->start_time;

        if ($mode == 'user') {
            $time = PHPWS_Time::getUserTime($time);
        } elseif ($mode == 'server') {
            $time = PHPWS_Time::getUserTime($time);
        }

        return strftime($format, $time);
    }


    function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    /**
     * Returns a linkable summary (if linked is true)
     */
    function getSummary($linked=true)
    {
        if ($linked) {
            $vars['view']   = 'event';
            $vars['event_id']     = $this->id;
            $vars['sch_id'] = $this->_schedule->id;
            $url = PHPWS_Text::linkAddress('calendar', $vars, false, true);
            return sprintf('<a href="%s" class="url">%s</a>', $url, $this->summary);
        } else {
            return $this->summary;
        }
    }

    function getTpl()
    {
        $tpl['SUMMARY']     = $this->getSummary();
        $tpl['DESCRIPTION'] = $this->getDescription();

        if (CALENDAR_MONTH_FIRST) {
            $month_day_mode = '%B %e';
        } else {
            $month_day_mode = '%e %B';
        }

        if ($this->all_day) {
            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                if (CALENDAR_MONTH_FIRST) {
                    $tpl['START_TIME'] =  sprintf(_('All day event, %s'), strftime('%B %e', $this->start_time));
                } else {
                    $tpl['START_TIME'] =  sprintf(_('All day event, %s'), strftime('%e', $this->start_time));
                }

                if (date('Ym', $this->start_time) != date('Ym', $this->end_time)) {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%B %e', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e', $this->end_time);
                    }
                } else {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%e', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e %B', $this->end_time);
                    }
                }
            } else {
                $tpl['START_TIME'] =  _('All day event');
                $tpl['END_TIME'] = $this->getStartTime($month_day_mode);
            }

            $tpl['DTSTART']     = PHPWS_Time::getDTTime($this->start_time);
            // Add one second to make end time midnight. Needed for hCalendar
            $tpl['DTEND']       = PHPWS_Time::getDTTime($this->end_time+1);
        } else {
            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                // If this event happens over 2 or more day
                $tpl['START_TIME'] = $this->getStartTime($month_day_mode . ', ' . CALENDAR_TIME_FORMAT);
                $tpl['END_TIME']   = $this->getEndTime($month_day_mode . ', ' . CALENDAR_TIME_FORMAT);
            } else {
                $tpl['START_TIME'] = $this->getStartTime($month_day_mode . ', ' . CALENDAR_TIME_FORMAT);
                $tpl['END_TIME']   = $this->getEndTime(CALENDAR_TIME_FORMAT);
            }
            $tpl['DTSTART']     = PHPWS_Time::getDTTime($this->start_time, 'user');
            $tpl['DTEND']       = PHPWS_Time::getDTTime($this->end_time, 'user');
        }


        if ( ($this->_schedule->public && Current_User::allow('calendar', 'edit_public', $this->_schedule->id)) ||
             (!$this->_schedule->public && Current_User::allow('calendar', 'edit_private', $this->_schedule->id))
             ) {
            $link[] = $this->editLink();
            $link[] = $this->deleteLink();
            $link[] = $this->repeatLink();
            $tpl['LINKS'] = implode(' | ', $link);
        }

        if (!empty($this->location)) {
            $tpl['LOCATION_LABEL'] = _('Location');

            if (!empty($this->loc_link)) {
                $tpl['LOCATION'] = sprintf('<a href="%s" title="%s">%s</a>',
                                           PHPWS_Text::checkLink($this->loc_link),
                                           _('Visit this location\'s web site.'),
                                           $this->location);
            } else {
                $tpl['LOCATION'] = $this->location;
            }
        }

        return $tpl;
    }

    function getViewLink()
    {
        $vars['view']   = 'event';
        $vars['event_id']     = $this->id;
        $vars['sch_id'] = $this->_schedule->id;
        return PHPWS_Text::linkAddress('calendar', $vars);
    }
    

    function init()
    {
        $table = $this->_schedule->getEventTable();
        
        if (empty($table)) {
            // error here
            return;
        }

        $db = & new PHPWS_DB($table);
        return $db->loadObject($this);
    }


    function monthDiff()
    {
        if (date('Ym', $this->start_time) != date('Ym', $this->end_time)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Posts the event information from the form into the object
     */
    function post()
    {
        if (empty($_POST['summary'])) {
            $errors[] = _('You must give your event a summary.');
        } else {
            $this->setSummary($_POST['summary']);
        }

        $this->setLocation($_POST['location']);
        $this->setDescription($_POST['description']);
        $this->setLocLink($_POST['loc_link']);
        if (isset($_POST['all_day'])) {
            $this->all_day = 1;
        } else {
            $this->all_day = 0;
        }

        if (isset($_POST['show_busy'])) {
            $this->show_busy = 1;
        } else {
            $this->show_busy = 0;
        }


        $start_date =  strtotime($_POST['start_date']);
        $end_date =  strtotime($_POST['end_date']);

        $start_time_hour   = &$_POST['start_time_hour'];
        $start_time_minute = &$_POST['start_time_minute'];
        $end_time_hour     = &$_POST['end_time_hour'];
        $end_time_minute   = &$_POST['end_time_minute'];

        if ($this->all_day) {
            $startTime = mktime(0,0,0, date('m', $start_date), date('d', $start_date),
                                date('Y', $start_date));
            $endTime = mktime(23,59,59, date('m', $end_date), (int)date('d', $end_date),
                                date('Y', $end_date));

        } else {
            $startTime = mktime($start_time_hour, $start_time_minute, 0,
                                date('m', $start_date), date('d', $start_date), date('Y', $start_date));
            $endTime   = mktime($end_time_hour, $end_time_minute, 0,
                                date('m', $end_date), date('d', $end_date), date('Y', $end_date));
        }

        if ($startTime >= $endTime) {
                $errors[] = _('The end time must be after the start time.');
        }

        $this->start_time = $startTime;
        $this->end_time   = $endTime;

        if (isset($_POST['sch_id'])) {
            $this->_sch_id = (int)$_POST['sch_id'];
        }

        if (isset($errors)) {
            $this->_error = &$errors;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Form for repeating events
     */
    function repeat($js=false)
    {
        $form = & new PHPWS_Form('repeat_event');

        $form->addHidden('module', 'calendar');
        $form->addHidden('sch_id', $this->_schedule->id);
        $form->addHidden('event_id', $this->id);
        $form->addHidden('aop', 'post_repeat');

        $form->addText('end_repeat_date', $this->getStartTime('%Y/%m/%d'));
        $form->setLabel('end_repeat_date', _('Repeat event until:'));

        $modes = array('daily',
                       'weekly',
                       'monthly',
                       'yearly',
                       'every');


        $modes_label = array(_('Daily'),
                             _('Weekly'),
                             _('Monthly'),
                             _('Yearly'),
                             _('Every'));

        $form->addRadio('repeat_mode', $modes);
        $form->setLabel('repeat_mode', $modes_label);

        $weekdays = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7);

        $weekday_labels = array(1=>strftime('%A', mktime(0,0,0,1,5,1970)),
                                2=>strftime('%A', mktime(0,0,0,1,6,1970)),
                                3=>strftime('%A', mktime(0,0,0,1,7,1970)),
                                4=>strftime('%A', mktime(0,0,0,1,8,1970)),
                                5=>strftime('%A', mktime(0,0,0,1,9,1970)),
                                6=>strftime('%A', mktime(0,0,0,1,10,1970)),
                                7=>strftime('%A', mktime(0,0,0,1,11,1970))
                                );

        $form->addCheck('weekday_repeat', $weekdays);
        $form->setLabel('weekday_repeat', $weekday_labels);



        $monthly = array('begin' => _('Beginning of each month'),
                         'end'   => _('End of each month'),
                         'start' => _('Every month on start date')
                         );

        $form->addSelect('monthly_repeat', $monthly);

        $every_repeat_week = array(1   => _('1st'),
                                   2   => _('2nd'),
                                   3   => _('3rd'),
                                   4   => _('4th'),
                                   'last' => _('Last')
                                   );

        $frequency = array('every' => _('Every month'),
                           1 => strftime('%B', mktime(0,0,0,1,1,1970)),
                           2 => strftime('%B', mktime(0,0,0,2,1,1970)),
                           3 => strftime('%B', mktime(0,0,0,3,1,1970)),
                           4 => strftime('%B', mktime(0,0,0,4,1,1970)),
                           5 => strftime('%B', mktime(0,0,0,5,1,1970)),
                           6 => strftime('%B', mktime(0,0,0,6,1,1970)),
                           7 => strftime('%B', mktime(0,0,0,7,1,1970)),
                           8 => strftime('%B', mktime(0,0,0,8,1,1970)),
                           9 => strftime('%B', mktime(0,0,0,9,1,1970)),
                           10 => strftime('%B', mktime(0,0,0,10,1,1970)),
                           11 => strftime('%B', mktime(0,0,0,11,1,1970)),
                           12 => strftime('%B', mktime(0,0,0,12,1,1970)));

        $form->addSelect('every_repeat_number', $every_repeat_week);
        $form->addSelect('every_repeat_weekday', $weekday_labels);
        $form->addSelect('every_repeat_frequency', $frequency);

        $form->addSubmit('submit', _('Repeat event'));
        if ($js) {
            $form->addHidden('js', 1);
            $form->addButton('cancel', _('Cancel'));
            $form->setExtra('cancel', 'onclick="window.close()"');
        }

        $tpl = $form->getTemplate();

        $js_vars['date_name'] = 'end_repeat_date';
        $tpl['END_CAL'] = javascript('js_calendar', $js_vars);


        return PHPWS_Template::process($tpl, 'calendar', 'admin/forms/repeat.tpl');
    }

    function repeatLink()
    {
        $linkvar['aop']      = 'repeat_event';
        $linkvar['sch_id']   = $this->_schedule->id;
        $linkvar['event_id'] = $this->id;

        if (javascriptEnabled()) {
            $linkvar['js'] = 1;
            $jsvars['address'] = PHPWS_Text::linkAddress('calendar', $linkvar);
            $jsvars['link_title'] = $jsvars['label'] = _('Repeat');
            $jsvars['width'] = CALENDAR_REPEAT_WIDTH;
            $jsvars['height'] = CALENDAR_REPEAT_HEIGHT;
            return javascript('open_window', $jsvars);
        } else {
            return PHPWS_Text::moduleLink(_('Repeat'), 'calendar', $linkvar);
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
            if (empty($this->key_id)) {
                $save_key = true;
            } else {
                $save_key = false;
            }
            $key = $this->saveKey();
            if (PEAR::isError($key)) {
                PHPWS_Error::log($key);
                return false;
            }

            $db->saveObject($this);

            $search = & new Search($this->key_id);
            $search->addKeywords($this->summary);
            $search->addKeywords($this->location);
            $search->addKeywords($this->description);
            $search->save();
            return TRUE;
        }
    }

    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = & new Key;
        } else {
            $key = & new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = & new Key;
            }
        }

        $key->setModule('calendar');
        $key->setItemName('event');
        $key->setItemId($this->id);
        //        $key->setEditPermission('edit_event');
        $key->setUrl($this->getViewLink());
        $key->setTitle($this->summary);
        if (!empty($this->description)) {
            $key->setSummary($this->description);
        }

        $result = $key->save();
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->key_id = $key->id;
        return $key;
    }


    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function setLocation($location)
    {
        if (empty($location)) {
            return;
        }
        $this->location = strip_tags($location);
    }

    function setLocLink($link)
    {
        if (empty($link)) {
            return;
        }

        $this->loc_link = strip_tags($link);
    }

    function setSummary($summary)
    {
        $this->summary = strip_tags($summary);
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