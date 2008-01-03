<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('CAL_VIEW_ALL',         1); // everyone can see this calendar
define('CAL_VIEW_SOME',        2); // most will see the open and close details only
define('CAL_VIEW_LIMIT',       3); // only people given express permission can view

PHPWS_Core::requireInc('calendar', 'error_defines.php');

class Calendar_Schedule {
    /**
     * @var integer
     */
    var $id          = 0;

    /**
     * Key id for associations
     * @var integer
     */
    var $key_id      = 0;

    /**
     * @var integer
     */
    var $title       = null;

    /**
     * information about the schedule
     * @var string
     */ 
    var $summary     = null;

    /**
     * User's id associated to this schedule. If zero
     * no association exists
     * @var integer
     */
    var $user_id     = 0;
    
    /**
     * Determines if anonymous users can see this schedule or
     * if they must be registered
     * @var boolean
     */
    var $public = true;

    /**
     * Name of contact for schedule
     * 
     * @var string
     */
    var $contact_name = null;

    /**
     * Email address of contact for schedule
     * 
     * @var string
     */
    var $contact_email = null;


    /**
     * Phone number of contact for schedule
     * 
     * @var string
     */
    var $contact_number = null;

    /**
     * if > 0, show upcoming events under mini calendar
     * 
     * @var integer
     */
    var $show_upcoming = 0;

    /**
     * Last error recorded by the class
     * @var object
     */
    var $_error         = null;

    /**
     * Key object for this schedule
     * @var object
     */
    var $_key           = null;


    function Calendar_Schedule($id=0)
    {
        if (!$id) {
            return;
        } else {
            $this->id = (int)$id;
            $this->init();
        }
    }


    function addEventLink($default_date=NULL)
    {
        if (!isset($default_date)) {
            $default_date = PHPWS_Time::getUserTime();
        }
        $add_label = dgettext('calendar', 'Add event');
        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&amp;aop=create_event&amp;js=1&amp;sch_id=%s&amp;date=%s',
                                       $this->id, $default_date);
            $vars['link_title'] = $vars['label'] = $add_label;
            $vars['width'] = CALENDAR_EVENT_WIDTH;
            $vars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink($add_label, 'calendar',
                                          array('aop'    => 'create_event',
                                                'sch_id' => $this->id,
                                                'date'   => $default_date)
                                          );
        }
    }

    function addSuggestLink($default_date=NULL)
    {
        if (!isset($default_date)) {
            $default_date = PHPWS_Time::getUserTime();
        }

        $suggest_label = dgettext('calendar', 'Suggest event');

        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&amp;uop=suggest_event&amp;js=1&amp;sch_id=%s&amp;date=%s',
                                       $this->id, $default_date);
            $vars['link_title'] = $vars['label'] = $suggest_label;
            $vars['width'] = CALENDAR_SUGGEST_WIDTH;
            $vars['height'] = CALENDAR_SUGGEST_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink($suggest_label, 'calendar',
                                          array('uop'    => 'suggest_event',
                                                'sch_id' => $this->id,
                                                'date'   => $default_date)
                                          );
        }
    }


    /**
     * Creates an event and repeat table for the schedule
     */
    function createEventTable()
    {
        $table = $this->getEventTable();
        $recurr = $this->getRecurrenceTable();
        if (empty($table) || empty($recurr)) {
            return PHPWS_Error::get(CAL_CANNOT_MAKE_EVENT_TABLE, 'calendar',
                                    'Calendar_Schedule::createEventTable');
        }
        
        $template['TABLE'] = $table;
        $template['RECURR_TABLE'] = $recurr;
        $template['INDEX_NAME'] = str_replace('_', '', $table) . '_idx';
        $template['RECURR_INDEX_NAME'] = str_replace('_', '', $recurr) . '_idx';

        $file = PHPWS_SOURCE_DIR . 'mod/calendar/inc/event_table.sql';

        if (!is_file($file)) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'calendar',
                                    'Calendar_Schedule::createEventTable', $file);
        }

        $query = PHPWS_Template::process($template, 'calendar', $file, true);
        return PHPWS_DB::import($query);
    }

    /**
     * Deletes a schedule from the database
     */
    function delete()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = $this->getDB();
        $db->addWhere('id', $this->id);

        printf('the id is %s and the current public is %s<br>', $this->id, PHPWS_Settings::get('calendar', 'public_schedule'));

        $result = $db->delete();

        if (!PEAR::isError($result)) {
            return PHPWS_DB::dropTable($this->getEventTable());
        } else {
            if (PHPWS_Settings::get('calendar', 'public_schedule') == $this->id) {
                PHPWS_Settings::set('calendar', 'public_schedule', 0);
                PHPWS_Settings::save('calendar');
            }
            return $result;
        }
    }


    /**
     * Edit form for a schedule
     */
    function form()
    {
        $key = $this->getKey();
        $form = new PHPWS_Form('schedule_form');

        if (isset($_REQUEST['js'])) {
            $form->addHidden('js', 1);
        }

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_schedule');
        $form->addHidden('sch_id', $this->id);

        $form->addText('title', $this->title);
        $form->setLabel('title', dgettext('calendar', 'Title'));
        $form->setSize('title', 40);

        $form->addTextArea('summary', $this->summary);
        $form->setLabel('summary', dgettext('calendar', 'Summary'));
        $form->useEditor('summary');

        if (PHPWS_Settings::get('calendar', 'personal_schedules')) {
            if (Current_User::allow('calendar', 'edit_public')) {
                $form->addRadio('public', array(0,1));
                $form->setLabel('public', array(dgettext('calendar', 'Private'),
                                                dgettext('calendar', 'Public')));
                $form->setMatch('public', (int)$this->public);
            } else {
                $form->addTplTag('PUBLIC', dgettext('calendar', 'Private'));
                $form->addHidden('public', 0);
            }
        } else {
            $form->addTplTag('PUBLIC', dgettext('calendar', 'Public'));
            $form->addHidden('public', 1);
        }

        $upcoming[0] = dgettext('calendar', 'Do not show upcoming events');
        $upcoming[1] = dgettext('calendar', 'Show upcoming week');
        $upcoming[2] = dgettext('calendar', 'Show next two weeks');
        $upcoming[3] = dgettext('calendar', 'Show upcoming month');

        $form->addSelect('show_upcoming', $upcoming);
        $form->setLabel('show_upcoming', dgettext('calendar', 'Show upcoming events'));
        $form->setMatch('show_upcoming', $this->show_upcoming);

        $form->addSubmit(dgettext('calendar', 'Save'));
        
        $template = $form->getTemplate();
        
        if (isset($_REQUEST['js'])) {
            $template['CLOSE'] = javascript('close_window', array('value' => dgettext('calendar', 'Cancel')));
        }
        
        $template['PUBLIC_LABEL'] = dgettext('calendar', 'Availability');
        return PHPWS_Template::process($template, 'calendar', 'admin/forms/edit_schedule.tpl');
    }

    function getCurrentUserSchedule()
    {
        $user_id = Current_User::getId();

        $schedule = new Calendar_Schedule;

        $db = Calendar_Schedule::getDB();
        $db->addWhere('user_id', $user_id);
        $result = $db->loadObject($schedule);
        if (PEAR::isError($result) || !$result) {
            return $result;
        } else {
            return $schedule;
        }
    }


    function &getDB() {
        $db = new PHPWS_DB('calendar_schedule');
        return $db;
    }

    function getEventTable()
    {
        if (!$this->id) {
            return NULL;
        } else {
            return sprintf('calendar_event_%s', $this->id);
        }
    }
    
    function &getKey()
    {
        if (!$this->_key) {
            $this->_key = new Key($this->key_id);
        }

        return $this->_key;
    }


    function getRecurrenceTable()
    {
        if (!$this->id) {
            return NULL;
        } else {
            return sprintf('calendar_recurr_%s', $this->id);
        }
    }


    function getViewLink($formatted=true)
    {
        $vars['sch_id'] = $this->id;

        if ($formatted) {
            return PHPWS_Text::moduleLink($this->title, 'calendar', $vars);
        } else {
            return PHPWS_Text::linkAddress('calendar', $vars);
        }
    }

    function init()
    {
        $db = $this->getDB();
        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->id = 0;
            PHPWS_Error::log($result);
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    function &loadEvent()
    {
        PHPWS_Core::initModClass('calendar', 'Event.php');

        if (!empty($_REQUEST['event_id'])) {
            $event = new Calendar_Event($this, (int)$_REQUEST['event_id']);
        } else {
            $event = new Calendar_Event($this);
        }

        return $event;
    }


    /**
     * Apply the results from the scheduler form
     */
    function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = dgettext('calendar', 'Missing title.');
            $this->title = null;
            return false;
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setSummary($_POST['summary']);
        $this->setPublic($_POST['public']);
        if (!$this->public && !$this->id) {
            $this->user_id = Current_User::getId();
        }

        $this->show_upcoming = (int)$_POST['show_upcoming'];

        return true;
    }

    function checkPermissions($authorized=false)
    {
        if ($this->public) {
            if ($authorized) {
                return Current_User::authorized('calendar', 'edit_public', $this->id, 'schedule');
            } else {
                return Current_User::allow('calendar', 'edit_public', $this->id, 'schedule');
            }
        } else {
            if ($authorized) {
                if ( Current_User::getAuthKey() == $_REQUEST['authkey'] &&
                     $this->user_id == Current_User::getId()) {
                    return true;
                } else {
                    return Current_User::authorized('calendar', 'edit_private', $this->id, 'schedule');
                }
            } else {
                if ($this->user_id == Current_User::getId()) {
                    return true;
                } else {
                    return Current_User::allow('calendar', 'edit_private', $this->id, 'schedule');
                }
            }
        }
    }


    function rowTags()
    {
        if ($this->checkPermissions()) {
            $links[] = $this->addEventLink();

            $vars = array('aop'=>'edit_schedule', 'sch_id' => $this->id);

            if (javascriptEnabled()) {
                $vars['js'] = 1;
                $js_vars['address'] = PHPWS_Text::linkAddress('calendar', $vars);
                $js_vars['label']   = dgettext('calendar', 'Edit');
                $js_vars['width']   = 640;
                $js_vars['height']  = 600;
                $links[] = javascript('open_window', $js_vars);
            } else {
                $links[] = PHPWS_Text::secureLink(dgettext('calendar', 'Edit'), 'calendar',
                                                  array('aop'=>'edit_schedule', 'sch_id'=>$this->id));
            }
        } 

        if (Current_User::allow('calendar', 'delete_schedule') && Current_User::isUnrestricted('calendar')) {
            $js['QUESTION'] = dgettext('calendar', 'Are you sure you want to delete this schedule?');
            $js['ADDRESS']  = sprintf('index.php?module=calendar&amp;aop=delete_schedule&amp;sch_id=%s&amp;authkey=%s',
                                      $this->id, Current_User::getAuthKey());
            $js['LINK']     = dgettext('calendar', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        if ($this->public && Current_User::isUnrestricted('calendar')) {
            $public_schedule = PHPWS_Settings::get('calendar', 'public_schedule');
            if ($public_schedule != $this->id) {
                $link_vars['aop'] = 'make_default_public';
                $link_vars['sch_id'] = $this->id;
                $links[] = PHPWS_Text::secureLink(dgettext('calendar', 'Make default public'), 'calendar', $link_vars);
            } else {
                $links[] = dgettext('calendar', 'Default public');
            }
        }

        if (!empty($links)) {
            $tags['ADMIN'] = implode(' | ', $links);
        } else {
            $tags['ADMIN'] = dgettext('calendar', 'None');
        }

        $tags['TITLE'] = $this->getViewLink();
        
        if ($this->public) {
            $tags['AVAILABILITY'] = dgettext('calendar', 'Public');
        } else {
            $tags['AVAILABILITY'] = dgettext('calendar', 'Private');
        }
        return $tags;
    }

    /**
     * Saves a schedule and creates a new event table if needed
     */
    function save()
    {
        $db = $this->getDB();
        if (empty($this->id)) {
            $new_key = TRUE;
        } else {
            $new_key = FALSE;
        }

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return false;
        } else {
            if (!PHPWS_DB::isTable($this->getEventTable())) {
                $result = $this->createEventTable();
                if (PEAR::isError($result)) {
                    $this->delete();
                    return $result;
                }
            }
            
            $result = $this->saveKey();
            if (PEAR::isError($result)) {
                $this->delete();
                return $result;
            }
            
            if ($new_key) {
                $db->saveObject($this);
            }

            return true;
        }
    }

    function getEvents($start_search, $end_search)
    {
        $event_table = $this->getEventTable();
        if (!$event_table) {
            return null;
        }

        PHPWS_Core::initModClass('calendar', 'Event.php');

        $db = new PHPWS_DB($event_table);

        $db->addWhere('start_time', $start_search, '>=', null,  'start');
        $db->addWhere('start_time', $end_search,   '<',  'AND', 'start');

        $db->addWhere('end_time',   $end_search,   '<=', null,  'end');
        $db->addWhere('end_time',   $start_search, '>',  'AND', 'end');

        $db->addWhere('start_time', $start_search, '<',  null,  'middle');
        $db->addWhere('end_time',   $end_search,   '>',  'AND', 'middle');

        $db->setGroupConj('end', 'OR');
        $db->setGroupConj('middle', 'OR');

        $db->addOrder('start_time');
        $db->addOrder('end_time desc');
        $db->setIndexBy('id');
        
        $result = $db->getObjects('Calendar_Event', $this);
        if (PHPWS_Error::logIfError($result)) {
            return null;
        }

        return $result;
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
        $key->setItemName('schedule');
        $key->setItemId($this->id);
        if ($this->public) {
            $key->restricted = 0;
            $key->setEditPermission('edit_public');
        } else {
            $key->restricted = 2;
            $key->setEditPermission('edit_private');
        }
        $key->setUrl($this->getViewLink(false));
        $key->setTitle($this->title);
        $key->setSummary($this->summary);
        $result = $key->save();

        $this->key_id = $key->id;
        return $result;
    }



    function setPublic($public)
    {
        $this->public = (bool)$public;
    }

    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

}

?>