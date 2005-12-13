<?php

  /**
   * These are the individuals calendars per user, object, room, etc.
   * They are called schedules to prevent Calendar_Calendar confusion :)
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_Schedule {
    var $id           = 0;
    var $key_id       = 0;
    var $user_id      = 0;
    var $title        = NULL;
    var $summary      = NULL; 
    var $public       = 0;
    var $display_name = NULL;

    // parent calendar object
    var $calendar     = NULL;
    // view object for displaying calendars
    var $view         = NULL;
    
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

    function loadView()
    {
        $this->view = & new Calendar_View;
        $this->view->schedule = & $this;
    }

    function view()
    {
        $this->loadView();
        return $this->view->month_grid('full');
    }

    function &getMonth($month=NULL, $year=NULL)
    {
        require_once 'Calendar/Month/Weekdays.php';
        if (!isset($month)) {
            $month = date('m');
        }

        if (!isset($year)) {
            $year = date('Y');
        }

        $oMonth = & new Calendar_Month_Weekdays($year, $month, PHPWS_Settings::get('calendar', 'starting_day'));
        $oMonth->build();
        return $oMonth;
    }


}

?>