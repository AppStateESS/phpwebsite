<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
define('CAL_VIEW_ALL', 1); // everyone can see this calendar
define('CAL_VIEW_SOME', 2); // most will see the open and close details only
define('CAL_VIEW_LIMIT', 3); // only people given express permission can view

\phpws\PHPWS_Core::requireInc('calendar', 'error_defines.php');
require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Admin.php';
require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Event.php';

class Calendar_Schedule
{

    /**
     * @var integer
     */
    public $id = 0;

    /**
     * Key id for associations
     * @var integer
     */
    public $key_id = 0;

    /**
     * @var integer
     */
    public $title = null;

    /**
     * information about the schedule
     * @var string
     */
    public $summary = null;

    /**
     * User's id associated to this schedule. If zero
     * no association exists
     * @var integer
     */
    public $user_id = 0;

    /**
     * Determines if anonymous users can see this schedule or
     * if they must be registered
     * @var boolean
     */
    public $public = true;

    /**
     * Name of contact for schedule
     *
     * @var string
     */
    public $contact_name = null;

    /**
     * Email address of contact for schedule
     *
     * @var string
     */
    public $contact_email = null;

    /**
     * Phone number of contact for schedule
     *
     * @var string
     */
    public $contact_number = null;

    /**
     * if > 0, show upcoming events under mini calendar
     *
     * @var integer
     */
    public $show_upcoming = 0;

    /**
     * Last error recorded by the class
     * @var object
     */
    public $_error = null;

    /**
     * Key object for this schedule
     * @var object
     */
    public $_key = null;

    public function __construct($id = 0)
    {
        if (!$id) {
            return;
        } else {
            $this->id = (int) $id;
            $this->init();
        }
    }

    function downloadEventsLink($label = null, $icon = false)
    {
        $vars['aop'] = 'download_event';
        $vars['sch_id'] = $this->id;
        $vars['js'] = 1;

        if (empty($label)) {
            $label = 'Download iCal events';
        }

        if ($icon) {
            $label = Icon::show('download', $label);
        }

        return PHPWS_Text::secureLink($label, 'calendar', $vars);
    }

    function uploadEventsLink($label = null, $icon = false)
    {
        $vars['aop'] = 'upload_event';
        $vars['sch_id'] = $this->id;
        $vars['js'] = 1;

        if (empty($label)) {
            $label = 'Upload iCal events';
        }
        if ($icon) {
            $label = Icon::show('upload');
        }

        $js['address'] = PHPWS_Text::linkAddress('calendar', $vars, 1);
        $js['width'] = 400;
        $js['height'] = 210;
        $js['label'] = $label;
        return javascript('open_window', $js);
    }

    public function addEventLink($default_date = NULL, $icon = false,
            $icon_only = false)
    {
        if (!isset($default_date)) {
            $default_date = PHPWS_Time::getUserTime();
        }

        if ($icon_only) {
            $add_label = '<i class="fa fa-plus"></i>';
        } elseif ($icon) {
            $add_label = '<i class="fa fa-plus"></i> ' . dgettext('calendar',
                            'Add event');
        } else {
            $add_label = 'Add event';
        }

        if (javascriptEnabled()) {
            $vars['address'] = sprintf('index.php?module=calendar&amp;aop=create_event&amp;js=1&amp;sch_id=%s&amp;date=%s',
                    $this->id, $default_date);
            $vars['link_title'] = 'Add event';
            $vars['label'] = $add_label;
            $vars['width'] = CALENDAR_EVENT_WIDTH;
            $vars['height'] = CALENDAR_EVENT_HEIGHT;
            return javascript('open_window', $vars);
        } else {
            return PHPWS_Text::moduleLink($add_label, 'calendar',
                            array('aop' => 'create_event', 'sch_id' => $this->id, 'date' => $default_date),
                            null, null, 'btn btn-success');
        }
    }

    public function addSuggestLink($default_date = NULL)
    {
        if (!isset($default_date)) {
            $default_date = PHPWS_Time::getUserTime();
        }

        $suggest_label = 'Suggest event';

        $event = new Calendar_Event(0, $this);
        $suggest_form = Calendar_Admin::event_form($event, true);

        $modal = <<<EOF
<div class="modal fade" id="suggestEvent" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document" style="width:800px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Suggest event</h4>
      </div>
      <div class="modal-body">
         $suggest_form
      </div>
      <div class="modal-footer">
        <button type="submit" onClick="$('#event_form').submit();" class="btn btn-success">Suggest event</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<button class="btn btn-primary" onClick="$('#suggestEvent').modal('show')">Suggest event</button>
EOF;

        return $modal;

    }

    /**
     * Creates an event and repeat table for the schedule
     */
    public function createEventTable()
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
    public function delete()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = $this->getDB();
        $db->addWhere('id', $this->id);

        $result = $db->delete();

        if (!PHPWS_Error::isError($result)) {
            $db2 = new PHPWS_DB('phpws_key');
            $db2->addWhere('module', 'calendar');
            $db2->addWhere('item_name', 'event' . $this->id);
            PHPWS_Error::logIfError($db2->delete());
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
    public function form()
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
        $form->setLabel('title', 'Title');
        $form->setSize('title', 40);

        $form->addTextArea('summary', $this->summary);
        $form->setLabel('summary', 'Summary');
        $form->useEditor('summary');

        if (PHPWS_Settings::get('calendar', 'personal_schedules')) {
            if (Current_User::allow('calendar', 'edit_public')) {
                $form->addRadio('public', array(0, 1));
                $form->setLabel('public',
                        array('Private',
                    'Public'));
                $form->setMatch('public', (int) $this->public);
            } else {
                $form->addTplTag('PUBLIC', 'Private');
                $form->addHidden('public', 0);
            }
        } else {
            $form->addTplTag('PUBLIC', 'Public');
            $form->addHidden('public', 1);
        }

        $upcoming[0] = 'Do not show upcoming events';
        $upcoming[1] = 'Show upcoming week';
        $upcoming[2] = 'Show next two weeks';
        $upcoming[3] = 'Show upcoming month';

        $form->addSelect('show_upcoming', $upcoming);
        $form->setLabel('show_upcoming',
                'Show upcoming events');
        $form->setMatch('show_upcoming', $this->show_upcoming);

        $form->addSubmit('Save');

        $template = $form->getTemplate();

        if (isset($_REQUEST['js'])) {
            $template['CLOSE'] = javascript('close_window',
                    array('value' => 'Cancel'));
        }

        $template['PUBLIC_LABEL'] = 'Availability';
        return PHPWS_Template::process($template, 'calendar',
                        'admin/forms/edit_schedule.tpl');
    }

    public function getDB()
    {
        $db = new PHPWS_DB('calendar_schedule');
        return $db;
    }

    public function getEventTable()
    {
        if (!$this->id) {
            return NULL;
        } else {
            return sprintf('calendar_event_%s', $this->id);
        }
    }

    public function getKey()
    {
        if (!$this->_key) {
            $this->_key = new \Canopy\Key($this->key_id);
        }

        return $this->_key;
    }

    public function getRecurrenceTable()
    {
        if (!$this->id) {
            return NULL;
        } else {
            return sprintf('calendar_recurr_%s', $this->id);
        }
    }

    public function getViewLink($formatted = true)
    {
        $vars['sch_id'] = $this->id;

        if ($formatted) {
            return PHPWS_Text::moduleLink($this->title, 'calendar', $vars);
        } else {
            return PHPWS_Text::linkAddress('calendar', $vars);
        }
    }

    public function init()
    {
        $db = $this->getDB();
        if (empty($this->id)) {
            return;
        }
        $result = $db->loadObject($this);

        if (PHPWS_Error::isError($result)) {
            $this->id = 0;
            PHPWS_Error::log($result);
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    public function loadEvent()
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');

        if (!empty($_REQUEST['event_id'])) {
            $event = new Calendar_Event((int) $_REQUEST['event_id'], $this);
        } else {
            $event = new Calendar_Event(0, $this);
        }

        return $event;
    }

    /**
     * Apply the results from the scheduler form
     */
    public function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = 'Missing title.';
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

        $this->show_upcoming = (int) $_POST['show_upcoming'];

        return true;
    }

    public function checkPermissions($authorized = false)
    {
        if ($this->public) {
            if ($authorized) {
                return Current_User::authorized('calendar', 'edit_public',
                                $this->id, 'schedule');
            } else {
                return Current_User::allow('calendar', 'edit_public', $this->id,
                                'schedule');
            }
        } else {
            if ($authorized) {
                if (Current_User::getAuthKey() == $_REQUEST['authkey'] &&
                        $this->user_id == Current_User::getId()) {
                    return true;
                } else {
                    return Current_User::authorized('calendar', 'edit_private',
                                    $this->id, 'schedule');
                }
            } else {
                if ($this->user_id == Current_User::getId()) {
                    return true;
                } else {
                    return Current_User::allow('calendar', 'edit_private',
                                    $this->id, 'schedule');
                }
            }
        }
    }

    public function rowTags()
    {
        if ($this->checkPermissions()) {
            $links[] = '<i class="fa fa-plus add-event" style="cursor:pointer" data-schedule-id="' . $this->id . '" data-date="' . time() . '"></i>';
            //$links[] = $this->addEventLink(null, true, true);
            $links[] = $this->uploadEventsLink(null, true);
            $links[] = $this->downloadEventsLink(null, true);
            $links[] = '<i class="fa fa-edit" id="edit-schedule" data-schedule-id="' .
                    $this->id . '" style="cursor:pointer" title="' . dgettext('calendar',
                            'Edit schedule') . '"></i>';
        }

        if (Current_User::allow('calendar', 'delete_schedule') && Current_User::isUnrestricted('calendar')) {
            $js['QUESTION'] = dgettext('calendar',
                    'Are you sure you want to delete this schedule?');
            $js['ADDRESS'] = sprintf('index.php?module=calendar&amp;aop=delete_schedule&amp;sch_id=%s&amp;authkey=%s',
                    $this->id, Current_User::getAuthKey());
            $js['LINK'] = Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        if ($this->public && Current_User::isUnrestricted('calendar')) {
            $public_schedule = PHPWS_Settings::get('calendar', 'public_schedule');
            if ($public_schedule != $this->id) {
                $link_vars['aop'] = 'make_default_public';
                $link_vars['sch_id'] = $this->id;
                $links[] = PHPWS_Text::secureLink(dgettext('calendar',
                                        'Make default public'), 'calendar',
                                $link_vars);
            } else {
                $links[] = 'Default public';
            }
        }

        if (!empty($links)) {
            $tags['ADMIN'] = implode(' ', $links);
        } else {
            $tags['ADMIN'] = 'None';
        }

        $tags['TITLE'] = $this->getViewLink();

        if ($this->public) {
            $tags['AVAILABILITY'] = 'Public';
        } else {
            $tags['AVAILABILITY'] = 'Private';
        }
        return $tags;
    }

    /**
     * Saves a schedule and creates a new event table if needed
     */
    public function save()
    {
        $db = $this->getDB();
        if (empty($this->id)) {
            $new_key = TRUE;
        } else {
            $new_key = FALSE;
        }

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return false;
        } else {
            if (!PHPWS_DB::isTable($this->getEventTable())) {
                $result = $this->createEventTable();
                if (PHPWS_Error::isError($result)) {
                    $this->delete();
                    return $result;
                }
            }

            $result = $this->saveKey();
            if (PHPWS_Error::isError($result)) {
                $this->delete();
                return $result;
            }

            if ($new_key) {
                $db->saveObject($this);
            }

            return true;
        }
    }

    public function getEvents($start_search, $end_search)
    {
        if (empty($start_search) || empty($end_search)) {
            return null;
        }

        $event_table = $this->getEventTable();
        if (!$event_table) {
            return null;
        }

        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');

        $db = new PHPWS_DB($event_table);

        $db->addWhere('start_time', $start_search, '>=', null, 'start');
        $db->addWhere('start_time', $end_search, '<', 'AND', 'start');

        $db->addWhere('end_time', $end_search, '<=', null, 'end');
        $db->addWhere('end_time', $start_search, '>', 'AND', 'end');

        $db->addWhere('start_time', $start_search, '<', null, 'middle');
        $db->addWhere('end_time', $end_search, '>', 'AND', 'middle');

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

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new \Canopy\Key;
        } else {
            $key = new \Canopy\Key($this->key_id);
            if (PHPWS_Error::isError($key->getError())) {
                $key = new \Canopy\Key;
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

    public function setPublic($public)
    {
        $this->public = (bool) $public;
    }

    public function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function exportEvent($event_id)
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');
        $event = new Calendar_Event($event_id, $this);
        if ($event->id) {
            $tpl = $event->icalTags();
        } else {
            $tpl['EMPTY'] = ' ';
        }

        $content = PHPWS_Template::process($tpl, 'calendar', 'ical.tpl');
        header("Content-type: text/calendar");
        header('Content-Disposition: attachment; filename="icalexport.ics"');
        echo $content;
        exit();
    }

    function exportEvents($start_time, $end_time)
    {
        $start_time = (int) $start_time;
        $end_time = (int) $end_time;

        if (empty($start_time) || empty($end_time) ||
                $start_time > $end_time) {
            $events = null;
        } else {
            $events = $this->getEvents((int) $start_time, (int) $end_time);
        }

        if (!empty($events)) {
            foreach ($events as $event) {
                $tpl = $event->icalTags();
                $master_tpl['event'][] = $tpl;
            }
        } else {
            $master_tpl['EMPTY'] = ' ';
        }

        $content = PHPWS_Template::process($master_tpl, 'calendar', 'ical.tpl');
        header("Content-type: text/calendar");
        header('Content-Disposition: attachment; filename="icalexport.ics"');
        echo $content;
        exit();
    }

    function allowICalDownload()
    {
        if ($this->id &&
                ( ( $this->public && ( Current_User::isLogged() || PHPWS_Settings::get('calendar',
                        'anon_ical') ) ) ||
                $this->checkPermissions() )
        ) {
            return true;
        } else {
            return false;
        }
    }

}
