<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('CAL_VIEW_ALL',         1); // everyone can see this calendar
define('CAL_VIEW_SOME',        2); // most will see the open and close details only
define('CAL_VIEW_LIMIT',       3); // only people given express permission can view

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
     * Determines if everyone can view the events
     * or everyone sees blanks and only certain people see events
     * or no one besides specific users may view
     * @var integer
     */
    var $view_status = CAL_VIEW_ALL;

    
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
     * Last error recorded by the class
     * @var object
     */
    var $_error         = null;


    /**
     * Array of events loaded into the object
     * @var array
     */
    var $_event_list    = null;

    var $_sorted_list   = null;

    /**
     * Array of event pointers keyed to month, day, year, and hour
     */
    var $_ordered_list  = null;


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
            $default_date = PHPWS_Time::mkservertime();
        }

        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&amp;aop=create_event&amp;js=1&amp;sch_id=%s&amp;date=%s',
                                       $this->id, $default_date);
            $vars['link_title'] = $vars['label'] = _('Add event');
            $vars['width'] = CALENDAR_EVENT_WIDTH;
            $vars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink(_('Add event'), 'calendar',
                                          array('aop'         => 'create_event',
                                                'sch_id' => $this->id,
                                                'date'        => $default_date)
                                          );
        }
    }

    function createEventTable()
    {
        $table = $this->getEventTable();
        if (empty($table)) {
            return null;
        }
        
        $template['TABLE'] = $table;
        $query = PHPWS_Template::process($template, 'calendar', 'admin/event_table.tpl');

        return PHPWS_DB::query($query);
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

        $result = $db->delete();

        if (!PEAR::isError($result)) {
            return PHPWS_DB::dropTable($this->getEventTable());
        } else {
            return $result;
        }
    }


    /**
     * Edit form for a schedule
     */
    function form()
    {
        $key = $this->getKey();
        $form = & new PHPWS_Form('schedule_form');

        if (isset($_REQUEST['js'])) {
            $form->addHidden('js', 1);
        }

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_schedule');
        $form->addHidden('sch_id', $this->id);

        $form->addText('title', $this->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 40);

        $form->addTextArea('summary', $this->summary);
        $form->setLabel('summary', _('Summary'));
        $form->useEditor('summary');

        $form->addRadio('public', array(0,1));
        $form->setLabel('public', array(_('Only registered users may view'),
                                             _('Anyone may view')));

        $form->setMatch('public', (int)$this->public);

        $form->addRadio('view_status', array(CAL_VIEW_ALL, CAL_VIEW_SOME, CAL_VIEW_LIMIT));
        $form->setLabel('view_status', array(_('Events details seen'),
                                             _('Event details hidden'),
                                             _('Schedule seen by permission only')));
        $form->setMatch('view_status', $this->view_status);

        $groups = Users_Permission::getPermissionGroups($key);
        $group_list = $groups['permitted']['users'];

        if (!empty($group_list)) {
            $select_list[0] = _('Not assigned');
            foreach ($group_list as $grp) {
                $select_list[$grp['user_id']] = $grp['name'];
            }
            $form->addSelect('user_id', $select_list);
            $form->setLabel('user_id', _('Assign to'));
        }

        $form->addSubmit(_('Save'));
        
        $template = $form->getTemplate();
        
        if (isset($_REQUEST['js'])) {
            $template['CLOSE'] = javascript('close_window', array('value' => _('Cancel')));
        }
        
        $template['PUBLIC_LABEL'] = _('Availability');
        $template['VIEW_STATUS_LABEL'] = _('Event view status');

        return PHPWS_Template::process($template, 'calendar', 'admin/forms/edit_schedule.tpl');
    }

    function getCurrentUserSchedule()
    {
        $user_id = Current_User::getId();

        $schedule = & new Calendar_Schedule;

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
        $db = & new PHPWS_DB('calendar_schedule');
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
        $key = & new Key($this->key_id);
        return $key;
    }


    function getEvents($start_search=NULL, $end_search=NULL, $schedules=NULL) {

        PHPWS_Core::initModClass('calendar', 'Event.php');
        if (!isset($start_search)) {
            $start_search = mktime(0,0,0,1,1,1970);
        } 

        if (!isset($end_search)) {
            // if this line is a problem, you need to upgrade
            $end_search = mktime(0,0,0,1,1,2050);
        }

        $db = & new PHPWS_DB($this->getEventTable());

        $db->addWhere('start_time', $start_search, '>=', NULL, 'start');
        $db->addWhere('start_time', $end_search,   '<',  'AND', 'start');

        $db->addWhere('end_time', $end_search,   '<=', 'NULL', 'end');
        $db->addWhere('end_time', $start_search, '>', 'AND', 'end');

        $db->setGroupConj('end', 'OR');

        $db->addOrder('start_time');
        $db->addOrder('end_time desc');
        $db->setIndexBy('id');

        $result = $db->getObjects('Calendar_Event', $this->id);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        return $result;
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
        $db->loadObject($this);
    }

    function &loadEvent()
    {
        PHPWS_Core::initModClass('calendar', 'Event.php');

        if (!empty($_REQUEST['event_id'])) {
            $event = & new Calendar_Event($this, (int)$_REQUEST['event_id']);
        } else {
            $event = & new Calendar_Event($this);
        }

        return $event;
    }

    function loadEventList($start_search=NULL, $end_search=NULL)
    {
        $result = $this->getEvents($start_search, $end_search, $this->id);
        $this->_event_list = & $result;
        $this->sortEvents();
        return TRUE;
    }

    /**
     * Apply the results from the scheduler form
     */
    function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = _('Missing title.');
            $this->title = null;
            return false;
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setSummary($_POST['summary']);
        $this->setPublic($_POST['public']);
        $this->setViewStatus($_POST['view_status']);

        return true;
    }


    function rowTags()
    {
        if (Current_User::allow('calendar', 'edit_schedule', $this->id)) {
            $links[] = $this->addEventLink();

            $vars = array('aop'=>'edit_schedule', 'sch_id' => $this->id);

            if (javascriptEnabled()) {
                $vars['js'] = 1;
                $js_vars['address'] = PHPWS_Text::linkAddress('calendar', $vars);
                $js_vars['label']   = _('Edit');
                $js_vars['width']   = 640;
                $js_vars['height']  = 600;
                $links[] = javascript('open_window', $js_vars);
            } else {
                $links[] = PHPWS_Text::secureLink(_('Edit'), 'calendar',
                                                  array('aop'=>'edit_schedule', 'sch_id'=>$this->id));
            }
        } 

        if (Current_User::allow('calendar', 'delete_schedule', $this->id)) {
            $js['QUESTION'] = _('Are you sure you want to delete this schedule?');
            //$js['QUESTION'] .= ' ' . _('All private, exclusive events will be deleted.');

            $js['ADDRESS']  = sprintf('index.php?module=calendar&amp;aop=delete_schedule&amp;sch_id=%s&amp;authkey=%s',
                                      $this->id, Current_User::getAuthKey());
            $js['LINK']     = _('Delete');
            $links[] = javascript('confirm', $js);
        }

        if ($this->public && Current_User::isUnrestricted('calendar')) {
            $public_schedule = PHPWS_Settings::get('calendar', 'public_schedule');
            if ($public_schedule != $this->id) {
                $link_vars['aop'] = 'make_default_public';
                $link_vars['sch_id'] = $this->id;
                $links[] = PHPWS_Text::secureLink(_('Make default public'), 'calendar', $link_vars);
            } else {
                $links[] = _('Default public');
            }
        }

        if (!empty($links)) {
            $tags['ADMIN'] = implode(' | ', $links);
        } else {
            $tags['ADMIN'] = _('None');
        }

        $tags['TITLE'] = $this->getViewLink();

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
        if ($this->public) {
            $key->setEditPermission('edit_public');
        } else {
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

    function setViewStatus($status)
    {
        $this->view_status = (int)$status;
    }

    function sortEvents()
    {
        if (empty($this->_event_list)) {
            return;
        }

        foreach ($this->_event_list as $key => $event) {
            $year = (int)date('Y', $event->start_time);
            $month = (int)date('m', $event->start_time);
            $day = (int)date('d', $event->start_time);
            $hour = (int)date('H', $event->start_time);
            $this->_sorted_list[$year]['events'][$key] = & $this->_event_list[$key];
            $this->_sorted_list[$year]['months'][$month]['events'][$key] = & $this->_event_list[$key];
            $this->_sorted_list[$year]['months'][$month]['days'][$day]['events'][$key] = & $this->_event_list[$key];
            $this->_sorted_list[$year]['months'][$month]['days'][$day]['hours'][$hour]['events'][$key] = & $this->_event_list[$key];
        }
    }

    function view()
    {

    }

}

?>