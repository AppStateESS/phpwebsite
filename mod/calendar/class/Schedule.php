<?php

  /**
   * These are the individuals calendars per user, object, room, etc.
   * They are called schedules to prevent Calendar_Calendar confusion :)
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_Schedule {
    var $id       = 0;
    var $key_id   = 0;
    var $user_id  = 0;
    var $title    = NULL;
    var $summary  = NULL; 
    var $public   = 0;
    var $events   = NULL;

    // hour the day view will start
    var $day_view_start = 0;

    // hour the day view will end
    var $day_view_end   = 0;

    // when viewing a week or month, day the week
    // starts (0 - Sun, 1 - Mon, etc.)
    var $start_week     = 0;

    var $display_name = NULL;

    // parent calendar object
    var $calendar     = NULL;

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

    function addEventLink()
    {
        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&aop=create_event_js&schedule_id=%s',
                                       $this->id);
            $var['link_title'] = $vars['label'] = _('Add event');
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Add event'), 'calendar',
                                          array('aop'         => 'create_event',
                                                'schedule_id' => $this->id)
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

    function loadEvents()
    {
        $db = & new PHPWS_DB('calendar_events');
        $db->addWhere('calendar_schedule_to_event.schedule_id', $this->id);
        $result = $db->getObjects('Calendar_Event');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return;
        }

        $this->events = & $result;
    }
}

?>