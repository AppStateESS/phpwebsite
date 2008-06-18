<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('CALENDAR_EVENT_TYPE_NORMAL',  1);
define('CALENDAR_EVENT_TYPE_ALL_DAY', 2);
define('CALENDAR_EVENT_TYPE_STARTS',  3);
define('CALENDAR_EVENT_TYPE_ENDS'  ,  4);

PHPWS_Core::requireInc('calendar', 'error_defines.php');

if (!defined('CALENDAR_SAME_DAY_MDY')) {
    define('CALENDAR_SAME_DAY_MDY', true);
 }

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
     * If true (1), then display as "Busy"
     * @var integer
     */
    var $show_busy   = 0;

    /**
     * contains the repeat type of the event
     * @var string
     */ 
    var $repeat_type = null;

    var $end_repeat = 0;

    /**
     * Parent id of a repeating event
     * @var integer
     */
    var $pid = 0;

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

    /**
     * Set to true if event was a repeat on load
     */
    var $_previous_repeat = false;

    /**
     * A hash of settings that determines if an event
     * has changed enough from the last setting to warrant
     * new repeat copies.
     */
    var $_previous_settings = null;

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

    function clearRepeats()
    {
        $table = $this->_schedule->getEventTable();
        $db = new PHPWS_DB($table);

        $db->addWhere('pid', $this->id);
        return $db->delete();
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

    function delete()
    {
        $table = $this->_schedule->getEventTable();

        Key::drop($this->key_id);

        $db = new PHPWS_DB($table);
        $db->addWhere('id', $this->id);
        
        // Remove any possible children
        $db->addWhere('pid', $this->id, null, 'or');
        PHPWS_Cache::clearCache();
        return $db->delete();
    }

    function deleteLink()
    {
        if (javascriptEnabled()) {
            $vars['QUESTION'] = dgettext('calendar', 'Are you sure you want to permanently delete this event?');
            $vars['ADDRESS'] = PHPWS_Text::linkAddress('calendar', array('aop' => 'delete_event',
                                                                         'sch_id' => $this->_schedule->id,
                                                                         'event_id' => $this->id), true);
            $vars['LINK']    = dgettext('calendar', 'Delete');
            return javascript('confirm', $vars);
        } else {
            return PHPWS_Text::secureLink(dgettext('calendar', 'Delete'), 'calendar',
                                          array('aop'         => 'delete_event',
                                                'sch_id' => $this->_schedule->id,
                                                'event_id'    => $this->id
                                                )
                                          );
        }
    }


    function blogLink()
    {
        $var['aop']      = 'blog_event';
        $var['sch_id']   = $this->_schedule->id;
        $var['event_id'] = $this->id;
        $var['js']       = 1;

        $js['address'] = PHPWS_Text::linkAddress('calendar', $var, true);
        $js['label'] = dgettext('calendar', 'Blog this');
        $js['width'] = '320';
        $js['height'] = '240';
        return javascript('open_window', $js);
    }


    function editLink($full=false)
    {
        $linkvar['aop']      = 'edit_event';
        $linkvar['sch_id']   = $this->_schedule->id;
        $linkvar['event_id'] = $this->id;
        
        if ($full) {
            $link_label = dgettext('calendar', 'Edit event');
        } else {
            $link_label = dgettext('calendar', 'Edit');
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
            $this->_key = new Key($this->key_id);
        }
        $this->_key->flag();
    }

    /**
     * The variables hashed determine if an event needs to have its copies
     * recreated
     */
    function getCurrentHash()
    {
        $hash[] = $this->repeat_type;
        $hash[] = $this->end_repeat;
        $hash[] = $this->start_time;
        $hash[] = $this->end_time;
        return md5(implode('', $hash));
    }

    function getEndRepeat($format='%c', $mode=null)
    {
        $time = &$this->end_repeat;

        if (!$time) {
            $time = $this->end_time;
        }

        if ($mode == 'user') {
            $time = PHPWS_Time::getUserTime($time);
        } elseif ($mode == 'server') {
            $time = PHPWS_Time::getUserTime($time);
        }

        return strftime($format, $time);
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


    function getKey()
    {
        if (!$this->_key) {
            $this->_key = new Key($this->key_id);
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
            $url = $this->getViewLink();
            return sprintf('<a href="%s" class="url">%s</a>', $url, $this->summary);
        } else {
            return $this->summary;
        }
    }

    function tplFormatTime()
    {
        static $tpl = null;

        if (CALENDAR_MONTH_FIRST) {
            $month_day_mode = '%B %e';
        } else {
            $month_day_mode = '%e %B';
        }

        if ($this->all_day) {
            $tpl['TO'] = '&ndash;';
            $tpl['START_TIME'] = $this->getStartTime();

            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                if (CALENDAR_MONTH_FIRST) {
                    if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%B %e, %Y', $this->start_time));
                    } else {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%B %e', $this->start_time));
                    }
                } else {
                    if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%e, %Y', $this->start_time));
                    } else {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%e', $this->start_time));
                    }
                }

                if (date('Ym', $this->start_time) != date('Ym', $this->end_time)) {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%B %e, %Y', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e, %Y', $this->end_time);
                    }
                } else {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%e, %Y', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e %B, %Y', $this->end_time);
                    }
                }
            } else {
                $tpl['START_TIME'] =  dgettext('calendar', 'All day event');
                $tpl['END_TIME'] = $this->getStartTime($month_day_mode);
            }

            $tpl['DTSTART']     = PHPWS_Time::getDTTime($this->start_time, 'all_day');
            if (CALENDAR_HCAL_ALLDAY_END) {
                $tpl['DTEND']       = PHPWS_Time::getDTTime($this->end_time + 1, 'all_day');
            }
        } else {
            // Not an all day event
            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                // If this event happens over 2 or more day
                if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                    $tpl['START_TIME'] = $this->getStartTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
                } else {
                    $tpl['START_TIME'] = $this->getStartTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode);
                }
                $tpl['END_TIME']   = $this->getEndTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
            } else {
                $tpl['START_TIME']   = $this->getStartTime(CALENDAR_TIME_FORMAT);
                if (CALENDAR_SAME_DAY_MDY) {
                    $tpl['END_TIME'] = $this->getEndTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
                } else {
                    $tpl['END_TIME'] = $this->getEndTime(CALENDAR_TIME_FORMAT);
                }
            }
            $tpl['DTSTART']     = PHPWS_Time::getDTTime($this->start_time, 'user');
            $tpl['DTEND']       = PHPWS_Time::getDTTime($this->end_time, 'user');
            $tpl['TO'] = dgettext('calendar', 'to');
        }

        return $tpl;
    }


    function getLocation()
    {
        if (!empty($this->loc_link)) {
            return sprintf('<a href="%s" title="%s">%s</a>',
                                       PHPWS_Text::checkLink($this->loc_link),
                                       dgettext('calendar', 'Visit this location\'s web site.'),
                                       $this->location);
        } else {
            return $this->location;
        }
    }

    function getTpl()
    {
        $tpl = $this->tplFormatTime();

        if ( $this->show_busy && !$this->_schedule->checkPermissions() ) {
            $tpl['SUMMARY']     = dgettext('calendar', 'Busy');
            $tpl['DESCRIPTION'] = null;
        } else {
            $tpl['SUMMARY']     = $this->getSummary();
            $tpl['DESCRIPTION'] = $this->getDescription();
        }

        if ($this->_schedule->checkPermissions()) {
            $link[] = $this->editLink();
            $link[] = $this->deleteLink();
            if (PHPWS_Core::moduleExists('blog')) {
                if (Current_User::allow('blog', 'edit_blog', null, null, true)) {
                    $link[] = $this->blogLink();
                }
            }
            $tpl['LINKS'] = implode(' | ', $link);
        }

        
        if (!empty($this->location)) {
            $tpl['LOCATION_LABEL'] = dgettext('calendar', 'Location');
            $tpl['LOCATION'] = $this->getLocation();
        }

        $tpl['BACK_LINK'] = PHPWS_Text::backLink();
        
        return $tpl;
    }

    function getViewLink()
    {
        $vars['view']     = 'event';
        $vars['event_id'] = $this->id;
        $vars['sch_id']   = $this->_schedule->id;
        return PHPWS_Text::linkAddress('calendar', $vars);
    }
    

    function init()
    {
        $table = $this->_schedule->getEventTable();
        
        if (empty($table)) {
            return PHPWS_Error::get(CAL_EVENT_TABLE_MISSING, 'calendar', 'Calendar::init', $table);
        }

        $db = new PHPWS_DB($table);
        return $db->loadObject($this);
    }


    function loadPrevious()
    {
        if (isset($this->repeat_type)) {
            $this->_previous_repeat = true;
            $this->_previous_settings = $this->getCurrentHash();
        }
    }


    function loadSchedule($id)
    {
        $this->_schedule = new Calendar_Schedule($id);
        if ($this->_schedule->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if this event is copy of another event
     */
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
    function post($suggested=false)
    {
        if (empty($_POST['summary'])) {
            $errors[] = dgettext('calendar', 'You must give your event a summary.');
        } else {
            $this->setSummary($_POST['summary']);
        }

        $this->setLocation($_POST['location']);
        $this->setDescription($_POST['description'], $suggested);
        $this->setLocLink($_POST['loc_link']);
        if (isset($_POST['all_day'])) {
            $this->all_day = 1;
        } else {
            $this->all_day = 0;
        }

        if (!$suggested && isset($_POST['show_busy'])) {
            $this->show_busy = 1;
        } else {
            $this->show_busy = 0;
        }


        $start_date =  strtotime($_POST['start_date']);
        $end_date =  strtotime($_POST['end_date']);

        if ($start_date > $end_date) {
            $end_date = $start_date;
        }

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
            $errors[] = dgettext('calendar', 'The end time must be after the start time.');
        }

        $this->start_time = $startTime;
        $this->end_time   = $endTime;

        if (isset($_POST['sch_id'])) {
            $this->_sch_id = (int)$_POST['sch_id'];
        }

        /********** Check repeats ************/
        if (!$suggested && isset($_POST['repeat_event'])) {
            $this->end_repeat = strtotime($_POST['end_repeat_date']) + 86399;

            if (date('Ymd', $this->end_repeat) <= date('Ymd', $this->start_time)) {
                $errors[] = dgettext('calendar', 'The date to repeat until must be greater than the event\'s start date.');
            }

            if (isset($_POST['repeat_mode'])) {

                switch ($_POST['repeat_mode']) {
                case 'daily':
                case 'yearly':
                    $this->repeat_type = $_POST['repeat_mode'];
                    break;

                case 'weekly':
                    $this->repeat_type = 'weekly';
                    if (empty($_POST['weekday_repeat'])) {
                        $errors[] = dgettext('calendar', 'Weekly repeats require you pick one or more days.');
                    } else {
                        $this->repeat_type .= ':' . implode(';', $_POST['weekday_repeat']);
                    }
                    break;

                case 'monthly':
                    $this->repeat_type = 'monthly';
                    if (empty($_POST['monthly_repeat'])) {
                        $errors[] = dgettext('calendar', 'Please pick a monthly repeat method.');
                        break;
                    }
                    switch ($_POST['monthly_repeat']) {
                    case 'begin':
                    case 'end':
                    case 'start':
                        $this->repeat_type .= ':' . $_POST['monthly_repeat'];
                        break;

                    default:
                        $errors[] = dgettext('calendar', 'Please pick a proper monthly repeat method.');
                    }
                    break;
                    
                case 'every':
                    if ( empty($_POST['every_repeat_number']) ||
                         empty($_POST['every_repeat_weekday']) ||
                         empty($_POST['every_repeat_frequency']) ) {
                        $errors[] = dgettext('calendar', 'Please choose options for the "Every" repeat method.');
                        break;
                    }
                    
                    $this->repeat_type = sprintf('every:%s;%s;%s',
                                                 $_POST['every_repeat_number'],
                                                 $_POST['every_repeat_weekday'],
                                                 $_POST['every_repeat_frequency']);

                    break;
                }
            } else {
                $errors[] = dgettext('calendar', 'You must choose a repeat mode.');
            }
        } else {
            $this->repeat_type = null;
            $this->end_repeat = 0;
        }

        if (isset($errors)) {
            $this->_error = &$errors;
            return false;
        } else {
            return true;
        }
    }


    /**
     * Makes a clone event
     */
    function repeatClone()
    {
        $clone = clone($this);
        $clone->pid         = $this->id;
        $clone->repeat_type = null;
        $clone->end_repeat  = 0;
        $clone->key_id      = $this->key_id;
        return $clone;
    }


    function save()
    {
        PHPWS_Core::initModClass('search', 'Search.php');

        $table = $this->_schedule->getEventTable();

        if (!PHPWS_DB::isTable($table)) {
            return PHPWS_Error::get(CAL_EVENT_TABLE_MISSING, 'calendar', 'Calendar_Event::save');
        }
        
        $db = new PHPWS_DB($table);
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        } elseif (!$this->pid) {
            // only save the key if the pid is 0
            // ie source event not copy
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

            if ($save_key) {
                $db->saveObject($this);
            }
            
            /* save search settings */
            $search = new Search($this->key_id);
            $search->addKeywords($this->summary);
            $search->addKeywords($this->location);
            $search->addKeywords($this->description);
            $search->save();
            return true;
        } 
    }

    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('calendar');
        $key->setItemName('event');
        $key->setItemId($this->id);

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


    function setDescription($description, $suggested=false)
    {
        if ($suggested) {
            $description = strip_tags($description);
        }

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

    function updateRepeats()
    {

        // this is a repeated copy of an event, no need to update
        // likewise if repeat type is null
        if ($this->pid || empty($this->repeat_type)) {
            return true;
        }

        $table = $this->_schedule->getEventTable();
        $db = new PHPWS_DB($table);

        $saveVals['summary']     = $this->summary;
        $saveVals['location']    = $this->location;
        $saveVals['loc_link']    = $this->loc_link;
        $saveVals['description'] = $this->description;
        $saveVals['show_busy']   = $this->show_busy;

        $db->addWhere('pid', $this->id);
        $db->addValue($saveVals);

        return $db->update();
    }

}


?>