<?php

  /**
   * These are the individuals calendars per user, object, room, etc.
   * They are called schedules to prevent Calendar_Calendar confusion :)
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_Schedule {
    /**
     * primary id of schedule
     * @access public
     * @var integer
     */
    var $id           = 0;

    /**
     * id of key associated to schedule
     * @access public
     * @var integer
     */
    var $key_id       = 0;

    /**
     * id of user assigned to this schedule
     * 0 = no user
     * @access public
     * @var integer
     */
    var $user_id      = 0;

    /**
     * name of schedule
     * @access public
     * @var string
     */
    var $title        = NULL;

    /**
     * short summary of function of schedule
     * @access public
     * @var string
     */
    var $summary      = NULL; 

    /**
     * indicator of public status
     * 0 = private, 1 = viewable to public
     * @access public
     * @var integer
     */
    var $public       = 0;

    /**
     * list of events associated to this schedule
     * @access private
     * @var array
     */
    var $events       = NULL;

    /**
     * date/time of last update
     * @access public
     * @var integer
     */
    var $last_updates = 0;

    /**
     * hour the day view will start
     * @access private
     * @var integer
     */
    var $day_view_start = 0;

    /**
     * hour the day view will end
     * @access private
     * @var integer
     */
    var $day_view_end   = 0;

    /**
     * when viewing a week or month, day the week
     * starts (0 - Sun, 1 - Mon, etc.)
     * @access private
     * @var integer
     */
    var $start_week     = 0;

    /**
     * parent calendar object
     * @access private
     * @var object
     */
    var $calendar     = NULL;

    /**
     * holds current error
     * @access private
     * @var object
     */
    var $_error       = NULL;
    
    function Calendar_Schedule($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        if (!$this->id) {
            return;
        }

        $db = & new PHPWS_DB('calendar_schedule');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }
    }

    function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
    }

    function setUserID($user_id)
    {
        $this->user_id = (int)$user_id;
    }

    function delete()
    {
        $db = & new PHPWS_DB('calendar_schedule');
        $db->addWhere('id', $this->id);
    }

    function save()
    {
        $db = & new PHPWS_DB('calendar_schedule');
        if (empty($this->id)) {
            $new_key = TRUE;
        } else {
            $new_key = FALSE;
        }

        if ($this->day_view_start >= $this->day_view_end) {

            $this->day_view_start = PHPWS_Settings::get('calendar', 'default_day_start');
            $this->day_view_end = PHPWS_Settings::get('calendar', 'default_day_end');
        }

        $this->last_updated = PHPWS_Time::getUTCTime();

        $result = $db->saveObject($this);

        if (PEAR::isError($result)) {
            return $result;
        }

        $result = $this->saveKey();
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($new_key) {
            $db->saveObject($this);
        }
    }

    function getViewLink()
    {
        return sprintf('<a href="#">%s</a>', $this->title);
    }

    function addEventLink($default_date=NULL)
    {
        if (!isset($default_date)) {
            $default_date = PHPWS_Time::mkservertime();
        }

        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&aop=create_event_js&schedule_id=%s&date=%s',
                                       $this->id, $default_date);
            $vars['link_title'] = $vars['label'] = _('Add event');
            $vars['width'] = CALENDAR_EVENT_WIDTH;
            $vars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Add event'), 'calendar',
                                          array('aop'         => 'create_event',
                                                'schedule_id' => $this->id,
                                                'date'        => $default_date)
                                          );
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
        $key->setItemName('schedule');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_schedule');
        $key->setUrl($this->getViewLink(TRUE));
        $key->setTitle($this->title);
        $key->setSummary($this->summary);
        $result = $key->save();
        $this->key_id = $key->id;
        return $result;
    }


    function loadEvents($start_search=NULL, $end_search=NULL)
    {
        PHPWS_Core::initModClass('calendar', 'Event.php');
        if (!isset($start_search)) {
            $start_search = mktime(0,0,0,1,1,1970);
        }

        if (!isset($end_search)) {
            // if this line is a problem, you need to upgrade
            $end_search = mktime(0,0,0,1,1,2050);
        }

        $db = & new PHPWS_DB('calendar_events');
        $db->setDistinct(TRUE);

        $db->addWhere('calendar_schedule_to_event.schedule_id', $this->id);
        $db->addWhere('id', 'calendar_schedule_to_event.event_id');

        $db->addWhere('start_time', $start_search, '>=', NULL, 1);
        $db->addWhere('start_time', $end_search,   '<',  'AND', 1);

        $db->addWhere('end_time', $end_search,   '<=', 'NULL', 2);
        $db->addWhere('end_time', $start_search, '>', 'AND', 2);

        $db->setGroupConj(2, 'OR');
        $db->addOrder('start_time');
        $db->addOrder('end_time desc');

        $result = $db->getObjects('Calendar_Event');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return;
        }

        $this->events = & $result;
    }

    function rowTags()
    {
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'calendar',
                                          array('aop'=>'edit_schedule',
                                                'schedule_id'=>$this->id));
        $js['QUESTION'] = _('Are you sure you want to delete this calendar?');
        if (!$this->public) {
            $js['QUESTION'] .= ' ' . _('All private, exclusive events will be deleted.');
        }
        $js['ADDRESS']  = sprintf('index.php?module=calendar&amp;aop=delete_schedule&amp;schedule_id=%s&amp;authkey=%s',
                                  $this->id, Current_User::getAuthKey());
        $js['LINK']     = _('Delete');
        $links[] = javascript('confirm', $js);
        $tags['ADMIN'] = implode(' | ', $links);
        return $tags;
    }
}

?>