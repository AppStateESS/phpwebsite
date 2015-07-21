<?php

/**
 * Contains administrative functionality
 *
 * main : controls administrative routing
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
class Calendar_Admin {

    /**
     * @var pointer to the parent object
     */
    public $calendar = null;
    public $title = null;
    public $content = null;
    public $message = null;

    public function __construct()
    {
        if (!isset($_SESSION['Calendar_Admin_Message'])) {
            return NULL;
        }

        $this->message = $_SESSION['Calendar_Admin_Message'];
        unset($_SESSION['Calendar_Admin_Message']);
    }

    public function allowSchedulePost()
    {
        if (!Current_User::allow('calendar')) {
            return false;
        }

        if ($this->calendar->schedule->public) {
            return Current_User::authorized('calendar', 'edit_public');
        } else {
            // private schedule
            if ($this->calendar->schedule->id) {
                // previously created schedule
                if ($this->calendar->schedule->user_id == Current_User::getId()) {
                    return true;
                } else {
                    return Current_User::authorized('calendar', 'edit_private');
                }
            } else {
                // new schedule
                if (PHPWS_Settings::get('calendar', 'personal_schedules')) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function approval()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('calendar', 'Suggestion.php');

        $pager = new DBPager('calendar_suggestions', 'Calendar_Suggestion');
        $pager->setModule('calendar');
        $pager->setTemplate('admin/approval.tpl');
        $pager->setOrder('submitted', null, true);
        $pager->addRowTags('getTpl');
        $pager->addToggle('class="bgcolor2"');

        $page_tags['TITLE_LABEL'] = dgettext('calendar',
                'Title / Time / Description');
        $page_tags['LOCATION_LABEL'] = dgettext('calendar', 'Location');
        $page_tags['ACTION_LABEL'] = dgettext('calendar', 'Action');
        $pager->setEmptyMessage(dgettext('calendar',
                        'No suggestions to approve.'));
        $pager->addPageTags($page_tags);

        $this->title = dgettext('calendar', 'Suggested events');
        $this->content = $pager->get();
    }

    public function approveSuggestion($id)
    {
        if (!Current_User::authorized('calendar', 'edit_public') ||
                Current_User::isRestricted('calendar')) {
            PHPWS_Core::errorPage('403');
        }

        PHPWS_Core::initModClass('calendar', 'Suggestion.php');
        $suggestion = new Calendar_Suggestion((int) $id);
        if (!$suggestion->id) {
            PHPWS_Core::errorPage('404');
        }

        $values = PHPWS_Core::stripObjValues($suggestion);
        unset($values['id']);

        $event = new Calendar_Event;
        $event->loadSchedule($suggestion->schedule_id);
        $event->public = 1;
        PHPWS_Core::plugObject($event, $values);

        $result = $event->save();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        $suggestion->delete();
    }

    public function disapproveSuggestion($id)
    {
        if (!Current_User::authorized('calendar', 'edit_public') ||
                Current_User::isRestricted('calendar')) {
            PHPWS_Core::errorPage('403');
        }

        PHPWS_Core::initModClass('calendar', 'Suggestion.php');
        $suggestion = new Calendar_Suggestion((int) $id);
        if (!$suggestion->id) {
            PHPWS_Core::errorPage('404');
        }

        return $suggestion->delete();
    }

    public function editEvent($event)
    {
        if ($event->id) {
            $this->title = dgettext('calendar', 'Update event');
        } else {
            $this->title = dgettext('calendar', 'Create event');
        }

        $this->content = self::event_form($event);
    }

    public function editSchedule()
    {
        if ($this->calendar->schedule->id) {
            $this->title = dgettext('calendar', 'Update schedule');
        } else {
            $this->title = dgettext('calendar', 'Create schedule');
        }

        $this->content = $this->calendar->schedule->form();
    }

    /**
     * Creates the edit form for an event
     */
    public static function event_form(Calendar_Event $event, $suggest = false)
    {
        Layout::addStyle('calendar');
        javascript('datetimepicker');
        // the form id is linked to the check_date javascript
        $form = new PHPWS_Form('event_form');
        if (isset($_REQUEST['js'])) {
            $form->addHidden('js', 1);
        }

        $form->addHidden('module', 'calendar');
        if ($suggest) {
            $form->addHidden('uop', 'post_suggestion');
        } else {
            $form->addHidden('aop', 'post_event');
        }
        $form->addHidden('event_id', $event->id);
        $form->addHidden('sch_id', $event->_schedule->id);

        $form->addText('summary', $event->summary);
        $form->setLabel('summary', dgettext('calendar', 'Summary'));
        $form->setSize('summary', 60);

        $form->addText('location', $event->location);
        $form->setLabel('location', dgettext('calendar', 'Location'));
        $form->setSize('location', 60);

        $form->addText('loc_link', $event->loc_link);
        $form->setLabel('loc_link', dgettext('calendar', 'Location link'));
        $form->setSize('loc_link', 60);

        $form->addTextArea('description', $event->description);

        if ($suggest) {
            $form->setRows('description', 8);
            $form->setCols('description', 55);
        } else {
            $form->useEditor('description');
        }

        $form->setLabel('description', dgettext('calendar', 'Description'));

        $form->addText('start_date', $event->getStartTime('%Y/%m/%d'));
        $form->setLabel('start_date', dgettext('calendar', 'Start time'));
        $form->setExtra('start_date', 'onblur="check_start_date()"');

        $form->addText('end_date', $event->getEndTime('%Y/%m/%d'));
        $form->setLabel('end_date', dgettext('calendar', 'End time'));
        $form->setExtra('end_date',
                'onblur="check_end_date()" onfocus="check_start_date()"');

        $event->timeForm('start_time', $event->start_time, $form);
        $event->timeForm('end_time', $event->end_time, $form);

        $form->setExtra('start_time_hour', 'onchange="check_start_date()"');
        $form->setExtra('end_time_hour', 'onchange="check_end_date()"');

        $form->addCheck('all_day', 1);
        $form->setMatch('all_day', $event->all_day);
        $form->setLabel('all_day', dgettext('calendar', 'All day event'));
        $form->setExtra('all_day', 'onchange="alter_date(this)"');

        if (!$suggest) {
            $form->addCheck('show_busy', 1);
            $form->setMatch('show_busy', $event->show_busy);
            $form->setLabel('show_busy', dgettext('calendar', 'Show busy'));
        }

        if ($suggest) {
            $form->addSubmit('save', dgettext('calendar', 'Suggest event'));
        } else {
            // Suggested events are not allowed repeats
            /**
             * Repeat form elements
             */
            $form->addCheck('repeat_event', 1);
            $form->setLabel('repeat_event',
                    dgettext('calendar', 'Make a repeating event'));

            $form->addText('end_repeat_date', $event->getEndRepeat('%Y/%m/%d'));
            $form->setLabel('end_repeat_date',
                    dgettext('calendar', 'Repeat event until:'));

            $modes = array('daily',
                'weekly',
                'monthly',
                'yearly',
                'every');


            $modes_label = array(dgettext('calendar', 'Daily'),
                dgettext('calendar', 'Weekly'),
                dgettext('calendar', 'Monthly'),
                dgettext('calendar', 'Yearly'),
                dgettext('calendar', 'Every'));

            $form->addRadio('repeat_mode', $modes);
            $form->setLabel('repeat_mode', $modes_label);

            $weekdays = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7);

            $weekday_labels = array(1 => strftime('%A',
                        mktime(0, 0, 0, 1, 5, 1970)),
                2 => strftime('%A', mktime(0, 0, 0, 1, 6, 1970)),
                3 => strftime('%A', mktime(0, 0, 0, 1, 7, 1970)),
                4 => strftime('%A', mktime(0, 0, 0, 1, 8, 1970)),
                5 => strftime('%A', mktime(0, 0, 0, 1, 9, 1970)),
                6 => strftime('%A', mktime(0, 0, 0, 1, 10, 1970)),
                7 => strftime('%A', mktime(0, 0, 0, 1, 11, 1970))
            );

            $form->addCheck('weekday_repeat', $weekdays);
            $form->setLabel('weekday_repeat', $weekday_labels);

            $monthly = array('begin' => dgettext('calendar',
                        'Beginning of each month'),
                'end' => dgettext('calendar', 'End of each month'),
                'start' => dgettext('calendar', 'Every month on start date')
            );

            $form->addSelect('monthly_repeat', $monthly);

            $every_repeat_week = array(1 => dgettext('calendar', '1st'),
                2 => dgettext('calendar', '2nd'),
                3 => dgettext('calendar', '3rd'),
                4 => dgettext('calendar', '4th'),
                5 => dgettext('calendar', 'Last')
            );

            $frequency = array('every_month' => dgettext('calendar',
                        'Every month'),
                1 => strftime('%B', mktime(0, 0, 0, 1, 1, 1970)),
                2 => strftime('%B', mktime(0, 0, 0, 2, 1, 1970)),
                3 => strftime('%B', mktime(0, 0, 0, 3, 1, 1970)),
                4 => strftime('%B', mktime(0, 0, 0, 4, 1, 1970)),
                5 => strftime('%B', mktime(0, 0, 0, 5, 1, 1970)),
                6 => strftime('%B', mktime(0, 0, 0, 6, 1, 1970)),
                7 => strftime('%B', mktime(0, 0, 0, 7, 1, 1970)),
                8 => strftime('%B', mktime(0, 0, 0, 8, 1, 1970)),
                9 => strftime('%B', mktime(0, 0, 0, 9, 1, 1970)),
                10 => strftime('%B', mktime(0, 0, 0, 10, 1, 1970)),
                11 => strftime('%B', mktime(0, 0, 0, 11, 1, 1970)),
                12 => strftime('%B', mktime(0, 0, 0, 12, 1, 1970)));

            $form->addSelect('every_repeat_number', $every_repeat_week);
            $form->addSelect('every_repeat_weekday', $weekday_labels);
            $form->addSelect('every_repeat_frequency', $frequency);

            /* set repeat form matches */

            if (!empty($event->repeat_type)) {
                $repeat_info = explode(':', $event->repeat_type);
                $repeat_mode_match = $repeat_info[0];
                if (isset($repeat_info[1])) {
                    $repeat_vars = explode(';', $repeat_info[1]);
                } else {
                    $repeat_vars = null;
                }

                $form->setMatch('repeat_mode', $repeat_mode_match);

                switch ($repeat_mode_match) {
                    case 'weekly':
                        $form->setMatch('weekday_repeat', $repeat_vars);
                        break;

                    case 'monthly':
                        $form->setMatch('monthly_repeat', $repeat_vars[0]);
                        break;

                    case 'every':
                        $form->setMatch('every_repeat_number', $repeat_vars[0]);
                        $form->setMatch('every_repeat_weekday', $repeat_vars[1]);
                        $form->setMatch('every_repeat_frequency',
                                $repeat_vars[2]);
                        break;
                }

                $form->setMatch('repeat_event', 1);
            }


            if ($event->pid) {
                $form->addHidden('pid', $event->pid);
                // This is a repeat copy, if saved it removes it from the copy list
                $form->addSubmit('save',
                        dgettext('calendar', 'Save and remove repeat'));
                $form->setExtra('save',
                        sprintf('onclick="return confirm(\'%s\')"',
                                dgettext('calendar',
                                        'Remove event from repeat list?')));
            } elseif ($event->id && $event->repeat_type) {
                // This is event is a source repeating event
                // Save this
                // Not sure if coding this portion. commenting for now
                // $form->addSubmit('save_source', dgettext('calendar', 'Save this event only'));
                $form->addSubmit('save_copy',
                        dgettext('calendar', 'Save and apply to repeats'));
                $form->setExtra('save_copy',
                        sprintf('onclick="return confirm(\'%s\')"',
                                dgettext('calendar', 'Apply changes to repeats?')));
            } else {
                // this is a non-repeating event
                $form->addSubmit('save', dgettext('calendar', 'Save event'));
            }
        }

        $tpl = $form->getTemplate();

        if (!$suggest) {
            $tpl['EVENT_TAB'] = dgettext('calendar', 'Event');
            $tpl['REPEAT_TAB'] = dgettext('calendar', 'Repeat');
        }

        if (isset($event->_error)) {
            $tpl['ERROR'] = implode('<br />', $event->_error);
        }

        if ($event->pid) {
            $linkvar['aop'] = 'edit_event';
            $linkvar['sch_id'] = $event->_schedule->id;
            $linkvar['event_id'] = $event->pid;
            if (javascriptEnabled()) {
                $linkvar['js'] = 1;
            }

            $source_link = PHPWS_Text::moduleLink(dgettext('calendar',
                                    'Click here if you would prefer to edit the source event.'),
                            'calendar', $linkvar);
            $tpl['REPEAT_WARNING'] = dgettext('calendar',
                            'This is a repeat of another event.') . '<br />' . $source_link;
        }

        $tpl['SYNC'] = sprintf('<input type="button" style="display : none" id="sync-dates" onclick="sync_dates(); return false;" name="sync-dates" value="%s" />',
                dgettext('calendar', 'Sync dates'));

        if (javascriptEnabled()) {
            Layout::addJSHeader('<script src="'. PHPWS_SOURCE_HTTP . 'mod/calendar/javascript/edit_event/head.js"></script>');
            Layout::addJSHeader('<script src="'. PHPWS_SOURCE_HTTP . 'mod/calendar/javascript/check_date/head.js"></script>');
        }

        return PHPWS_Template::process($tpl, 'calendar',
                        'admin/forms/edit_event.tpl');
    }

    public function getPanel()
    {
        $panel = new PHPWS_Panel('calendar');

        $vars['aop'] = 'schedules';
        $tabs['schedules'] = array('title' => dgettext('calendar', 'Schedules'),
            'link' => PHPWS_Text::linkAddress('calendar', $vars));

        if (Current_User::allow('calendar', 'settings')) {
            $vars['aop'] = 'settings';
            $tabs['settings'] = array('title' => dgettext('calendar', 'Settings'),
                'link' => PHPWS_Text::linkAddress('calendar', $vars));
        }

        if (Current_User::isUnrestricted('calendar') && Current_User::allow('calendar',
                        'edit_public')) {
            $vars['aop'] = 'approval';
            $db = new PHPWS_DB('calendar_suggestions');
            $count = $db->count();
            if (PHPWS_Error::isError($count)) {
                PHPWS_Error::log($count);
                $count = 0;
            }
            $tabs['approval'] = array('title' => sprintf(dgettext('calendar',
                                'Approval (%s)'), $count),
                'link' => PHPWS_Text::linkAddress('calendar', $vars));
        }

        $panel->quickSetTabs($tabs);
        return $panel;
    }

    private function scheduleJSON($id)
    {
        $schedule = new Calendar_Schedule($id);
        $json['public'] = $schedule->public;
        $json['title'] = $schedule->title;
        $json['summary'] = html_entity_decode($schedule->summary);
        $json['show_upcoming'] = $schedule->show_upcoming;
        echo json_encode($json);
    }

    private function getEventJson()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Schedule.php';
        require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Event.php';
        $event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
        $schedule_id = filter_input(INPUT_GET, 'schedule_id',
                FILTER_VALIDATE_INT);

        if (empty($event_id)) {
            echo json_encode(array('error' => 'No event id'));
            exit();
        }

        $schedule = new Calendar_Schedule($schedule_id);
        $event = new Calendar_Event($event_id, $schedule);
        $repeat_event = !empty($event->repeat_type) ? 1 : 0;


        $repeat_info = explode(':', $event->repeat_type);
        $repeat_type = $repeat_info[0];
        if (isset($repeat_info[1])) {
            $repeat_vars = explode(';', $repeat_info[1]);
        } else {
            $repeat_vars = null;
        }

        if ($event->all_day) {
            $end_hour = $end_minute = 0;
        } else {
            $end_hour = (int) $event->getEndTime('%H');
            $end_minute = (int) $event->getEndTime('%M');
        }

        $json = array(
            'event_id' => $event->id,
            'summary' => $event->summary,
            'location' => $event->location,
            'loc_link' => $event->loc_link,
            'description' => $event->getDescription(),
            'all_day' => $event->all_day,
            'start_date' => $event->getStartTime('%Y/%m/%d'),
            'end_date' => $event->getEndTime('%Y/%m/%d'),
            'start_hour' => (int) $event->getStartTime('%H'),
            'start_minute' => (int) $event->getStartTime('%M'),
            'end_hour' => $end_hour,
            'end_minute' => $end_minute,
            'show_busy' => $event->show_busy,
            'repeat_event' => $repeat_event,
            'end_repeat_date' => $event->getEndRepeat('%Y/%m/%d'),
            'repeat_type' => $repeat_type,
            'repeat_vars' => $repeat_vars
        );

        echo json_encode($json);
        exit();
    }

    /**
     * routes administrative commands
     */
    public function main()
    {
        if (!Current_User::allow('calendar')) {
            Current_User::disallow();
            return;
        }

        $panel = $this->getPanel();

        if (isset($_REQUEST['aop'])) {
            $command = $_REQUEST['aop'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch ($command) {
            case 'get_event_json':
                $this->getEventJson();
                break;

            case 'post_event':
                if (!$this->calendar->schedule->checkPermissions(true)) {
                    Current_User::disallow();
                }
                $this->postEvent();
                break;

            case 'schedule_json':
                $this->scheduleJSON(filter_input(INPUT_GET, 'sch_id',
                                FILTER_SANITIZE_NUMBER_INT));
                exit();
                break;

            case 'approval':
                $this->approval();
                break;

            case 'approve_suggestion':
                $this->approveSuggestion($_GET['suggestion_id']);
                PHPWS_Core::goBack();
                break;


            case 'create_event':
                $panel->setCurrentTab('schedules');
                $event = $this->calendar->schedule->loadEvent();
                if ($this->calendar->current_date) {
                    $event->start_time = mktime(12, 0, 0,
                            $this->calendar->int_month,
                            $this->calendar->int_day, $this->calendar->int_year);

                    $event->end_time = mktime(12, 0, 0,
                            $this->calendar->int_month,
                            $this->calendar->int_day, $this->calendar->int_year);
                }

                $this->editEvent($event);
                break;

            case 'create_schedule':
                if (!Current_User::allow('calendar') ||
                        (!Current_User::allow('calendar', 'edit_public') &&
                        !PHPWS_Settings::get('calendar', 'personal_schedules'))) {
                    Current_User::disallow();
                }
                $this->calendar->schedule = new Calendar_Schedule;
                $panel->setCurrentTab('schedules');
                $this->editSchedule();
                break;

            case 'blog_event':
                if (PHPWS_Core::moduleExists('blog') &&
                        Current_User::allow('blog', 'edit_blog') &&
                        $this->calendar->schedule->checkPermissions(true)) {
                    $event = $this->calendar->schedule->loadEvent();
                    $this->blogEvent();
                }
                break;

            case 'post_blog':
                if (PHPWS_Core::moduleExists('blog') &&
                        Current_User::allow('blog', 'edit_blog') &&
                        $this->calendar->schedule->checkPermissions(true)) {
                    $this->postBlog();
                }
                javascript('close_refresh');
                Layout::nakedDisplay();
                break;

            case 'edit_event':
                $panel->setCurrentTab('schedules');
                if (!$this->calendar->schedule->checkPermissions()) {
                    Current_User::disallow();
                }
                $event = $this->calendar->schedule->loadEvent();
                $this->editEvent($event);
                break;

            case 'delete_event':
                if ($this->calendar->schedule->checkPermissions(true)) {
                    $event = $this->calendar->schedule->loadEvent();
                    $result = $event->delete();
                    if (PHPWS_Error::isError($result)) {
                        PHPWS_Error::log($result);
                    }
                }
                PHPWS_Core::goBack();
                break;

            case 'delete_schedule':
                if (Current_User::authorized('calendar', 'delete_schedule') && Current_User::isUnrestricted('calendar')) {
                    $this->calendar->schedule->delete();
                    $this->sendMessage(dgettext('calendar', 'Schedule deleted.'),
                            'aop=schedules');
                } else {
                    Current_User::disallow();
                }
                break;

            case 'disapprove_suggestion':
                $this->disapproveSuggestion($_GET['suggestion_id']);
                PHPWS_Core::goBack();
                break;

            case 'edit_schedule':
                if (empty($_REQUEST['sch_id'])) {
                    PHPWS_Core::errorPage('404');
                }

                if (!$this->calendar->schedule->checkPermissions()) {
                    Current_User::disallow();
                }
                $panel->setCurrentTab('schedules');
                $this->editSchedule();
                break;

            case 'make_default_public':
                if (Current_User::isUnrestricted('calendar')) {
                    PHPWS_Settings::set('calendar', 'public_schedule',
                            (int) $_REQUEST['sch_id']);
                    PHPWS_Settings::save('calendar');
                    $this->message = dgettext('calendar',
                            'Default public schedule set.');
                }
                $this->scheduleListing();
                break;

            case 'post_schedule':
                $this->postSchedule();
                break;

            case 'post_settings':
                if (!Current_User::authorized('calendar', 'settings')) {
                    Current_User::disallow();
                }
                $this->postSettings();
                $this->message = dgettext('calendar', 'Settings saved');
                $this->settings();
                break;

            case 'repeat_event':
                $panel->setCurrentTab('schedules');
                $event = $this->calendar->schedule->loadEvent();
                $this->repeatEvent($event);
                break;

            case 'reset_cache':
                if (!Current_User::allow('calendar')) {
                    Current_User::disallow();
                }
                PHPWS_Cache::remove($_REQUEST['key']);
                PHPWS_Core::goBack();
                break;

            case 'schedules':
                $panel->setCurrentTab('schedules');
                $this->scheduleListing();
                break;

            case 'settings':
                $this->settings();
                break;

            case 'upload_event':
                if (!$this->calendar->schedule->checkPermissions()) {
                    Current_User::disallow();
                }

                $this->uploadEvent();
                break;

            case 'post_upload':
                if (!$this->calendar->schedule->checkPermissions(true)) {
                    Current_User::disallow();
                }
                $this->postUpload();
                break;
        }

        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE'] = $this->title;

        if (is_array($this->message)) {
            $tpl['MESSAGE'] = implode('<br />', $this->message);
        } else {
            $tpl['MESSAGE'] = $this->message;
        }

        // Clears in case of js window opening
        $this->content = $this->title = $this->message = null;

        $final = PHPWS_Template::process($tpl, 'calendar', 'admin/main.tpl');

        if (PHPWS_Calendar::isJS()) {
            Layout::nakedDisplay($final);
        } else {
            $panel->setContent($final);
            Layout::add(PHPWS_ControlPanel::display($panel->display()));
        }
    }

    private function postUpload()
    {
        $error = false;
        if (empty($_FILES['upload_file']['tmp_name'])) {
            $error = true;
            $content[] = dgettext('calendar', 'Missing filename.');
        } elseif ($_FILES['upload_file']['type'] != 'text/calendar') {
            $error = true;
            $content[] = dgettext('calendar', 'Improper file format.');
        }

        if (!$error) {
            $result = file($_FILES['upload_file']['tmp_name']);

            if (!is_array($result)) {
                $error = true;
                $content[] = dgettext('calendar',
                        'Unable to parse file for events.');
            } elseif (trim($result[0]) != 'BEGIN:VCALENDAR') {
                $error = true;
                $content[] = dgettext('calendar',
                        'File does not appear to be in iCal/vCal format.');
            }
        }

        if ($error) {
            $content[] = $this->calendar->schedule->uploadEventsLink(false,
                    dgettext('calendar', 'Return to upload form...'));
            $this->title = dgettext('calendar', 'Error');
            $this->content = implode('<br />', $content);
            return;
        }

        PHPWS_Core::initModClass('calendar', 'Event.php');

        $table = $this->calendar->schedule->getEventTable();

        $db = new PHPWS_DB($table);

        $success = 0;
        $duplicates = 0;
        foreach ($result as $cal) {
            $cal = trim($cal);
            $colon = strpos($cal, ':');
            if (!$colon) {
                continue;
            }
            $command = substr($cal, 0, $colon);
            if ($semicolon = strpos($cal, ';')) {
                $command = substr($cal, 0, $semicolon);
            }
            $value = substr($cal, $colon + 1, strlen($cal));

            if (empty($value)) {
                continue;
            }
            switch ($command) {
                case 'BEGIN':
                    if ($value == 'VEVENT' && !isset($event)) {
                        $event = new Calendar_Event(0, $this->calendar->schedule);
                        $event->start_time = 0;
                        $event->end_time = 0;
                    }
                    break;

                case 'DTSTART':
                    if (isset($event)) {
                        $event->start_time = strtotime($value);
                    }
                    break;

                case 'DTSTART;VALUE=DATE':
                    if (isset($event)) {
                        $event->start_time = strtotime($value);
                    }
                    break;

                case 'DTEND':
                    if (isset($event)) {
                        $event->end_time = strtotime($value);
                    }
                    break;

                case 'DTEND;VALUE=DATE':
                    if (isset($event)) {
                        $event->end_time = strtotime($value);
                    }
                    break;


                case 'SUMMARY':
                    if (isset($event)) {
                        $value = str_replace('\,', ',', $value);
                        $event->setSummary($value, true);
                    }
                    break;

                case 'LOCATION':
                    if (isset($event)) {
                        $event->setLocation($value);
                    }
                    break;

                case 'DESCRIPTION':
                    if (isset($event)) {
                        $value = str_replace('\,', ',', $value);
                        $value = str_replace('\n', "\n", $value);
                        $event->setDescription($value, null, true);
                    }
                    break;

                case 'END':
                    if ($value == 'VEVENT' && isset($event)) {
                        if (empty($event->end_time)) {
                            //start time should be midnight so add 23h 23min 59 sec
                            $event->end_time = $event->start_time + 86399;
                            $event->all_day = 1;
                        }

                        $db->reset();
                        $db->addWhere('start_time', $event->start_time);
                        $db->addWhere('end_time', $event->end_time);
                        $db->addWhere('summary', $event->summary);
                        $db->addColumn('id');
                        $result = $db->select('one');

                        if (!empty($result)) {
                            if (PHPWS_Error::logIfError($result)) {
                                $parse_errors[] = dgettext('calendar',
                                        'Error accessing event table.');
                            } else {
                                $duplicates++;
                            }
                        } else {
                            $save = $event->save();

                            if (PHPWS_Error::logIfError($save) || !$save) {
                                $parse_errors[] = dgettext('calendar',
                                        'Error saving new event.');
                            } else {
                                $success++;
                            }
                        }
                        unset($event);
                    }
                    break;
            }
        }

        $this->title = dgettext('calendar', 'Import complete!');

        if (isset($parse_errors)) {
            $content[] = dgettext('calendar',
                    'The following errors occurred when trying to import your events:');
            $content[] = '<ul><li>' . implode('</li><li>', $parse_errors) . '</li></ul>';
        }

        $content[] = sprintf(dgettext('calendar',
                        '%s event(s) were successfully imported.'), $success);
        $content[] = sprintf(dgettext('calendar',
                        '%s duplicate event(s) were ignored.'), $duplicates);
        $content[] = javascript('close_window');
        $this->content = implode('<br />', $content);
    }

    private function uploadEvent()
    {
        $form = new PHPWS_Form('upload-event');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_upload');
        $form->addHidden('js', 1);
        $form->addHidden('sch_id', $this->calendar->schedule->id);
        $form->addFile('upload_file');
        $form->setLabel('upload_file', dgettext('calendar', 'File location'));
        $form->addSubmit('go', dgettext('calendar', 'Send file'));
        $tpl = $form->getTemplate();
        $tpl['CLOSE'] = javascript('close_window');
        $this->content = PHPWS_Template::process($tpl, 'calendar', 'upload.tpl');
        $this->title = dgettext('calendar', 'Import iCal/vCal file');
    }

    /**
     * Checks the legitimacy of the event and saves the results
     */
    public function postEvent()
    {
        $return_view = filter_input(INPUT_POST, 'return_view');

        if (!empty($return_view)) {
            $command = 'uop=' . $return_view;
        }

        $event = $this->calendar->schedule->loadEvent();
        $event->loadPrevious();
        if ($event->post()) {
            if ($event->pid) {
                /**
                 * if the pid is set, then it's saving a copy event
                 * copy events are changed to source events so
                 * the pid and key are reset
                 */
                $event->pid = 0;
                $event->key_id = 0;
            }

            $result = $event->save();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                if (PHPWS_Calendar::isJS()) {
                    Layout::nakedDisplay(dgettext('calendar',
                                    'An error occurred when saving your event.'));
                    exit();
                } else {
                    $this->sendMessage(dgettext('calendar',
                                    'An error occurred when saving your event.'),
                            'aop=schedules');
                }
            } else {
                $result = $this->saveRepeat($event);
                if (PHPWS_Error::isError($result)) {
                    if (PHPWS_Calendar::isJS()) {
                        PHPWS_Error::log($result);
                        Layout::nakedDisplay(dgettext('calendar',
                                        'An error occurred when trying to repeat an event.'),
                                'aop=schedules');
                        exit();
                    } else {
                        $this->sendMessage(dgettext('calendar',
                                        'An error occurred when trying to repeat an event.',
                                        'aop=schedules'));
                    }
                }

                PHPWS_Cache::remove(sprintf('grid_%s_%s_%s',
                                date('n', $event->start_time),
                                date('Y', $event->start_time),
                                $this->calendar->schedule->id));

                PHPWS_Cache::remove(sprintf('list_%s_%s_%s',
                                date('n', $event->start_time),
                                date('Y', $event->start_time),
                                $this->calendar->schedule->id));

                $view = filter_input(INPUT_POST, 'view');
                if (!empty($view)) {
                    $this->sendMessage(dgettext('calendar', 'Event saved.'),
                            'view=' . $view . '&date=' . $event->start_time . '&event_id=' . $event->id . '&sch_id=' . $this->calendar->schedule->id);
                } else {
                    $this->sendMessage(dgettext('calendar', 'Event saved.'),
                            'aop=schedules');
                }
            }
        } else {
            $this->editEvent($event);
        }
    }

    public function postSettings()
    {
        PHPWS_Settings::set('calendar', 'personal_schedules',
                (int) isset($_POST['personal_schedules']));
        PHPWS_Settings::set('calendar', 'allow_submissions',
                (int) isset($_POST['allow_submissions']));
        PHPWS_Settings::set('calendar', 'mini_event_link',
                (int) isset($_POST['mini_event_link']));
        PHPWS_Settings::set('calendar', 'cache_month_views',
                (int) isset($_POST['cache_month_views']));
        PHPWS_Settings::set('calendar', 'mini_grid',
                (int) isset($_POST['mini_grid']));
        PHPWS_Settings::set('calendar', 'anon_ical',
                (int) isset($_POST['anon_ical']));
        PHPWS_Settings::set('calendar', 'no_follow',
                (int) isset($_POST['no_follow']));

        PHPWS_Settings::set('calendar', 'display_mini',
                (int) $_POST['display_mini']);
        PHPWS_Settings::set('calendar', 'starting_day',
                (int) $_POST['starting_day']);
        PHPWS_Settings::set('calendar', 'default_view', $_POST['default_view']);
        PHPWS_Settings::set('calendar', 'brief_grid', $_POST['brief_grid']);

        PHPWS_Settings::save('calendar');
        PHPWS_Cache::clearCache();
    }

    /**
     * Saves a repeated event
     */
    public function saveRepeat(Calendar_Event $event)
    {

        // if this event has a parent id, don't try and save repeats
        if ($event->pid) {
            return true;
        }

        // This event is not repeating
        if (empty($event->repeat_type)) {
            // Previously, the event repeated, remove the copies
            $result = $event->clearRepeats();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
            }
            return true;
        }

        // Event is repeating
        // First check if the repeat scheme changed

        if ($event->_previous_repeat && $event->getCurrentHash() == $event->_previous_settings) {
            // The event has not changed, so we just update the repeats
            // that exist and return
            return $event->updateRepeats();
        }

        // The repeat setting changed or were never set, so need to recreate the copies
        $result = $event->clearRepeats();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }


        $repeat_info = explode(':', $event->repeat_type);
        $repeat_mode = $repeat_info[0];
        if (isset($repeat_info[1])) {
            $repeat_vars = explode(';', $repeat_info[1]);
        }

        switch ($repeat_mode) {
            case 'daily':
                $result = $this->repeatDaily($event);
                break;

            case 'weekly':
                $result = $this->repeatWeekly($event);
                break;

            case 'monthly':
                $result = $this->repeatMonthly($event);
                break;

            case 'yearly':
                $result = $this->repeatYearly($event);
                break;

            case 'every':
                $result = $this->repeatEvery($event);
                break;
        }

        if (!$result) {
            return false;
        }

        if (PHPWS_Error::isError($result)) {
            return $result;
        } else {
            return true;
        }
    }

    public function postSchedule()
    {
        $default_public = PHPWS_Settings::get('calendar', 'public_schedule');
        if ($this->calendar->schedule->post()) {
            if (!$this->allowSchedulePost()) {
                Current_User::disallow();
                return;
            }

            $result = $this->calendar->schedule->save();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                if (PHPWS_Calendar::isJS()) {
                    $this->sendMessage(dgettext('calendar',
                                    'An error occurred when saving your schedule.'),
                            null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(dgettext('calendar',
                                    'An error occurred when saving your schedule.'),
                            'aop=schedules');
                }
            } else {
                if ($this->calendar->schedule->public && ($default_public < 1)) {
                    PHPWS_Settings::set('calendar', 'public_schedule',
                            $this->calendar->schedule->id);
                    PHPWS_Settings::save('calendar');
                }

                if (!$this->calendar->schedule->public && $this->calendar->schedule->id == $default_public) {
                    PHPWS_Settings::set('calendar', 'public_schedule', 0);
                    PHPWS_Settings::save('calendar');
                }

                if (PHPWS_Calendar::isJS()) {
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(dgettext('calendar', 'Schedule saved.'),
                            'aop=schedules');
                }
            }
        } else {
            $this->message = $this->calendar->schedule->_error;
            $this->editSchedule();
        }
    }

    public function repeatDaily(Calendar_Event $event)
    {
        PHPWS_Core::requireConfig('calendar');

        $dst_start = date('I', $event->start_time);

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        while ($time_unit <= $event->end_repeat) {
            $copy_event->id = 0;

            $dst_current = date('I', $time_unit);
            if ($dst_current != $dst_start) {
                if ($dst_current) {
                    $time_unit -= 3600;
                } else {
                    $time_unit += 3600;
                }
                $dst_start = $dst_current;
            }

            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar',
                                'Calendar_Admin::repeatDaily');
            }

            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $time_unit += 86400;
            $result = $copy_event->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    public function repeatEvent($event)
    {
        if (!$event->id) {
            $this->content = dgettext('calendar', 'This event does not exist.');
            return;
        }

        $this->title = sprintf(dgettext('calendar', 'Repeat event - %s'),
                $event->summary);
        if (isset($_REQUEST['js']) && $_REQUEST['js']) {
            $js = true;
        } else {
            $js = false;
        }
        $this->content = $event->repeat($js);
    }

    public function repeatEvery(Calendar_Event $event)
    {

        if (!isset($_POST['monthly_repeat'])) {
            return false;
        }
        $dst_start = date('I', $event->start_time);
        $max_count = 0;

        $every_repeat_number = &$_POST['every_repeat_number'];
        $every_repeat_weekday = &$_POST['every_repeat_weekday'];
        $every_repeat_frequency = &$_POST['every_repeat_frequency'];

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        $repeat_days = &$_POST['weekday_repeat'];

        $weekday_count = 0;

        while ($time_unit < $event->end_repeat) {
            $copy_event->id = 0;

            $dst_current = date('I', $time_unit);
            if ($dst_current != $dst_start) {
                if ($dst_current) {
                    $time_unit -= 3600;
                } else {
                    $time_unit += 3600;
                }
                $dst_start = $dst_current;
            }

            // First check if we are in the correct month or if the repeat is in every month
            if ($every_repeat_frequency == 'every_month' || $every_repeat_frequency == (int) strftime('%m',
                            $time_unit)) {

                // next check if we are in the correct weekday
                $current_weekday = (int) strftime('%u', $time_unit);

                if ($current_weekday == $every_repeat_weekday) {
                    $current_day = strftime('%e', $time_unit);

                    // count the current weekday
                    $day_add = ($current_day % 7) ? 1 : 0;

                    $weekday_count = floor($current_day / 7) + $day_add;

                    /**
                     * if the current weekday count is equal to the repeat number
                     * --- OR---
                     * if the repeat is set to the last day of the month, and we are in the fourth weekday,
                     * and next week would put us over the end of the month, then post this day
                     */
                    if ($weekday_count == $every_repeat_number ||
                            ( $every_repeat_number == 5 && $weekday_count == 4 && ( ($current_day + 7) > date('t',
                                    $time_unit) ) )) {
                        $weekday_found = true;
                        $max_count++;

                        if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                            return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED,
                                            'calendar',
                                            'Calendar_Admin::repeatWeekly');
                        }

                        $copy_event->start_time = $time_unit;
                        $copy_event->end_time = $time_unit + $time_diff;
                        $result = $copy_event->save();
                        if (PHPWS_Error::isError($result)) {
                            return $result;
                        }
                    }
                }
            }


            $time_unit += 86400;
        }
    }

    public function repeatYearly(Calendar_Event $event)
    {
        $max_count = 0;
        if ((date('L', $event->start_time) && date('n', $event->start_time) == 2 && date('j',
                        $event->start_time) == 29) &&
                (date('L', $event->end_time) && date('n', $event->end_time) == 2 && date('j',
                        $event->end_time) == 29)) {
            $leap_year = true;
        } else {
            $leap_year = false;
        }
        printf('start time %s<br>', strftime('%c', $event->start_time));
        $c_hour = (int) strftime('%H', $event->start_time);
        $c_min = (int) strftime('%M', $event->start_time);
        $c_month = (int) strftime('%m', $event->start_time);
        $c_day = (int) strftime('%d', $event->start_time);
        $c_year = (int) strftime('%Y', $event->start_time);

        $time_diff = $event->end_time - $event->start_time;
        $copy_event = $event->repeatClone();

        // start count on year ahead
        if ($leap_year) {
            $ts_count = mktime($c_hour, $c_min, 0, $c_month, $c_day, $c_year + 4);
        } else {
            $ts_count = mktime($c_hour, $c_min, 0, $c_month, $c_day, $c_year + 1);
        }

        while ($ts_count <= $event->end_repeat) {
            $copy_event->id = 0;
            $c_hour = (int) strftime('%H', $ts_count);
            $c_min = (int) strftime('%M', $ts_count);
            $ts_month = $c_month = (int) strftime('%m', $ts_count);
            $ts_day = $c_day = (int) strftime('%d', $ts_count);
            $c_year = (int) strftime('%Y', $ts_count);

            $max_count++;

            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar',
                                'Calendar_Admin::repeatYearly');
            }

            $start_time = mktime($c_hour, $c_min, 0, $ts_month, $ts_day, $c_year);
            $copy_event->start_time = $start_time;
            $copy_event->end_time = $start_time + $time_diff;

            $result = $copy_event->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }

            if ($leap_year) {
                $ts_count = mktime($c_hour, $c_min, 0, $c_month, $c_day,
                        $c_year + 4);
            } else {
                $ts_count = mktime($c_hour, $c_min, 0, $c_month, $c_day,
                        $c_year + 1);
            }
        }
        return true;
    }

    public function repeatMonthly(Calendar_Event $event)
    {
        if (!isset($_POST['monthly_repeat'])) {
            return false;
        }

        $max_count = 0;

        $c_hour = (int) strftime('%H', $event->start_time);
        $c_min = (int) strftime('%M', $event->start_time);
        $c_month = (int) strftime('%m', $event->start_time);
        $c_day = (int) strftime('%d', $event->start_time);
        $c_year = (int) strftime('%Y', $event->start_time);


        $time_diff = $event->end_time - $event->start_time;
        $copy_event = $event->repeatClone();

        // start count on month ahead
        $ts_count = mktime($c_hour, $c_min, 0, $c_month + 1, $c_day, $c_year);

        while ($ts_count <= $event->end_repeat) {
            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar',
                                'Calendar_Admin::repeatMonthly');
            }

            $copy_event->id = 0;

            $c_hour = (int) strftime('%H', $ts_count);
            $c_min = (int) strftime('%M', $ts_count);
            $ts_month = $c_month = (int) strftime('%m', $ts_count);
            $ts_day = $c_day = (int) strftime('%d', $ts_count);
            $c_year = (int) strftime('%Y', $ts_count);

            switch ($_POST['monthly_repeat']) {
                case 'begin':
                    $ts_day = 1;
                    break;

                case 'end':
                    $ts_day = 0;
                    $ts_month++;
                    break;
            }

            $start_time = mktime($c_hour, $c_min, 0, $ts_month, $ts_day, $c_year);
            $copy_event->start_time = $start_time;
            $copy_event->end_time = $start_time + $time_diff;

            $result = $copy_event->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }

            $ts_count = mktime($c_hour, $c_min, 0, $c_month + 1, $c_day, $c_year);
        }

        return true;
    }

    public function repeatWeekly(Calendar_Event $event)
    {
        if (!isset($_POST['weekday_repeat']) || !is_array($_POST['weekday_repeat'])) {
            $this->message = dgettext('calendar',
                    'You must choose which weekdays to repeat.');
            return false;
        }

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        $repeat_days = &$_POST['weekday_repeat'];
        $dst_start = date('I', $event->start_time);
        while ($time_unit <= $event->end_repeat) {

            if (!in_array(strftime('%u', $time_unit), $repeat_days)) {
                $time_unit += 86400;
                continue;
            }

            $dst_current = date('I', $time_unit);
            if ($dst_current != $dst_start) {
                if ($dst_current) {
                    $time_unit -= 3600;
                } else {
                    $time_unit += 3600;
                }
                $dst_start = $dst_current;
            }

            $copy_event->id = 0;

            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar',
                                'Calendar_Admin::repeatWeekly');
            }

            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $result = $copy_event->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
            $time_unit += 86400;
        }
        return TRUE;
    }

    public static function eventModal($event)
    {
        $event_form = self::event_form($event);
        $modal = new \Modal('edit-event', $event_form, 'Edit Event');
        $modal->sizeLarge();
        $modal->addButton('<button class="btn btn-success" id="submit-event">Save</button>');
        return $modal->__toString();
    }

    public static function includeScheduleJS()
    {
        javascript('jquery');
        $filename = PHPWS_SOURCE_HTTP . 'mod/calendar/javascript/schedule.js';
        $authkey = \Current_User::getAuthKey();
        $script = "<script type='text/javascript' src='$filename'></script>" .
                "<script type='text/javascript'>var authkey='$authkey';</script>";
        \Layout::addJSHeader($script, 'cal_sched');
    }

    public static function includeAuthkey()
    {
        static $authkey_inserted = false;

        if ($authkey_inserted) {
            return;
        } else {
            $authkey = \Current_User::getAuthKey();
            $script = "<script type='text/javascript'>var authkey='$authkey';</script>";
            \Layout::addJSHeader($script, 'authkey');
            $authkey_inserted = true;
        }
    }

    public static function includeEventJS()
    {
        javascript('jquery');
        self::includeAuthkey();
        $filename = PHPWS_SOURCE_HTTP . 'mod/calendar/javascript/event.js';
        $script = "<script type='text/javascript' src='$filename'></script>";
        \Layout::addJSHeader($script, 'cal_event');
    }

    public function scheduleListing()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('calendar', 'Schedule.php');
        require_once(PHPWS_SOURCE_DIR . 'mod/calendar/class/Event.php');
        self::includeScheduleJS();
        self::includeEventJS();

        $schedule = new Calendar_Schedule;
        $schedule->id = 1;

        $this->title = dgettext('calendar', 'Schedules');

        $event = new Calendar_Event(0, $schedule);
        $page_tags['EVENT_FORM'] = self::eventModal($event);
        $page_tags['DESCRIPTION_LABEL'] = dgettext('calendar', 'Description');
        $page_tags['PUBLIC_LABEL'] = dgettext('calendar', 'Public');
        $page_tags['DISPLAY_NAME_LABEL'] = dgettext('calendar', 'User');
        $page_tags['AVAILABILITY_LABEL'] = dgettext('calendar', 'Availability');

        $page_tags['ADD_CALENDAR'] = '<button id="create-schedule" class="btn btn-success"><i class="fa fa-file-text"></i> ' . dgettext('calendar',
                        'Create schedule') . '</button>';

        $schedule_form = $this->calendar->schedule->form();
        $schedule_modal = new \Modal('schedule-modal', $schedule_form,
                'Create schedule');
        $schedule_modal->sizeLarge();
        $page_tags['SCHEDULE_FORM'] = $schedule_modal->__toString();
        $page_tags['ADMIN_LABEL'] = dgettext('calendar', 'Options');

        $pager = new DBPager('calendar_schedule', 'Calendar_Schedule');
        $pager->setModule('calendar');
        $pager->setTemplate('admin/schedules.tpl');
        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');
        $pager->addToggle('class="bgcolor1"');
        $pager->setEmptyMessage(dgettext('calendar',
                        'No schedules have been created.'));
        $pager->addSortHeader('title', dgettext('calendar', 'Title'));
        $pager->addSortHeader('public', dgettext('calendar', 'Availability'));

        $pager->db->addWhere('user_id', 0);
        $pager->db->addWhere('user_id', 'users.id', '=', 'or');

        $pager->db->addColumn('*');
        $pager->db->addColumn('users.display_name');
        $pager->db->addJoin('left', 'calendar_schedule', 'users', 'user_id',
                'id');

        $pager->initialize();
        $this->content = $pager->get();
    }

    public function sendMessage($message, $location = null)
    {
        $_SESSION['Calendar_Admin_Message'] = $message;
        if (empty($location)) {
            PHPWS_Core::goBack();
        } else {
            PHPWS_Core::reroute('index.php?module=calendar&' . $location);
            exit();
        }
    }

    public function settings()
    {
        $form = new PHPWS_Form('calendar_settings');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('allow_submissions', 1);
        $form->setMatch('allow_submissions',
                PHPWS_Settings::get('calendar', 'allow_submissions'));
        $form->setLabel('allow_submissions',
                dgettext('calendar', 'Allow public event submissions'));

        $form->addCheckbox('mini_event_link', 1);
        $form->setMatch('mini_event_link',
                PHPWS_Settings::get('calendar', 'mini_event_link'));
        $form->setLabel('mini_event_link',
                dgettext('calendar',
                        'Only link days with events in mini calendar'));

        $form->addCheckbox('anon_ical', 1);
        $form->setMatch('anon_ical',
                PHPWS_Settings::get('calendar', 'anon_ical'));
        $form->setLabel('anon_ical',
                dgettext('calendar',
                        'Allow anonymous iCal exports of public schedules'));

        $form->addCheckbox('no_follow', 1);
        $form->setMatch('no_follow',
                PHPWS_Settings::get('calendar', 'no_follow'));
        $form->setLabel('no_follow',
                dgettext('calendar',
                        'No follow directives added to navigation links'));

        $start_days = array(0, 1);
        $start_days_label[0] = strftime('%A', mktime(0, 0, 0, 1, 4, 1970));
        $start_days_label[1] = strftime('%A', mktime(0, 0, 0, 1, 5, 1970));
        $form->addRadio('starting_day', $start_days);
        $form->setLabel('starting_day', $start_days_label);
        $form->setMatch('starting_day',
                PHPWS_Settings::get('calendar', 'starting_day'));

        $form->addRadio('brief_grid', array(0, 1));
        $form->setMatch('brief_grid',
                PHPWS_Settings::get('calendar', 'brief_grid'));
        $form->setLabel('brief_grid',
                array(0 => dgettext('calendar', 'Show event titles'),
            1 => dgettext('calendar', 'Show number of events')));

        $form->addCheck('personal_schedules', 1);
        $form->setLabel('personal_schedules',
                dgettext('calendar', 'Allow personal schedules'));
        $form->setMatch('personal_schedules',
                PHPWS_Settings::get('calendar', 'personal_schedules'));

        $form->addCheck('cache_month_views', 1);
        $form->setLabel('cache_month_views',
                dgettext('calendar', 'Cache month views (public only)'));
        $form->setMatch('cache_month_views',
                PHPWS_Settings::get('calendar', 'cache_month_views'));

        $form->addCheck('mini_grid', 1);
        $form->setLabel('mini_grid', dgettext('calendar', 'Show mini grid'));
        $form->setMatch('mini_grid',
                PHPWS_Settings::get('calendar', 'mini_grid'));

        $form->addRadio('display_mini', array(0, 1, 2));
        $form->setLabel('display_mini',
                array(dgettext('calendar', 'Don\'t show'), dgettext('calendar',
                    'Only on front page'), dgettext('calendar', 'On all pages')));
        $form->setMatch('display_mini',
                PHPWS_Settings::get('calendar', 'display_mini'));

        $views['grid'] = dgettext('calendar', 'Month grid');
        $views['list'] = dgettext('calendar', 'Month list');
        $views['day'] = dgettext('calendar', 'Day view');
        $views['week'] = dgettext('calendar', 'Week view');

        $form->addSelect('default_view', $views);
        $form->setLabel('default_view', dgettext('calendar', 'Default view'));
        $form->setMatch('default_view',
                PHPWS_Settings::get('calendar', 'default_view'));

        $form->addSubmit(dgettext('calendar', 'Save settings'));
        $tpl = $form->getTemplate();

        $tpl['BRIEF_GRID_LABEL'] = dgettext('calendar', 'Grid event display');
        $tpl['MINI_CALENDAR'] = dgettext('calendar', 'Display mini calendar');

        $tpl['START_LABEL'] = dgettext('calendar', 'Week start day');

        $this->content = PHPWS_Template::process($tpl, 'calendar',
                        'admin/settings.tpl');
        $this->title = dgettext('calendar', 'Calendar settings');
    }

    public function blogEvent()
    {
        $event = $this->calendar->schedule->loadEvent();
        $form = new PHPWS_Form('blog_event');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_blog');
        $form->addHidden('event_id', $event->id);
        $form->addHidden('sch_id', $this->calendar->schedule->id);

        $advance[0] = dgettext('calendar', 'Date of occurence');
        $advance[1] = dgettext('calendar', 'A day prior');
        $advance[2] = dgettext('calendar', 'Two days prior');
        $advance[3] = dgettext('calendar', 'Three days prior');
        $advance[7] = dgettext('calendar', 'A week prior');
        $advance[14] = dgettext('calendar', 'Two weeks prior');
        $advance[30] = dgettext('calendar', 'One month prior');
        $form->addSelect('advance_post', $advance);
        $form->setLabel('advance_post',
                dgettext('calendar', 'When should it post?'));
        $form->addSubmit(dgettext('calendar', 'Post to Blog'));

        $tpl = $form->getTemplate();
        $tpl['CLOSE'] = javascript('close_window');
        $this->title = dgettext('calendar', 'Post Event to Blog');
        $this->content = PHPWS_Template::process($tpl, 'calendar',
                        'admin/forms/blog.tpl');
    }

    public function postBlog()
    {
        $event = $this->calendar->schedule->loadEvent();

        if (!PHPWS_Core::initModClass('blog', 'Blog.php')) {
            return;
        }
        $blog = new Blog;
        $blog->title = $event->summary;

        $tpl = $event->tplFormatTime();
        $summary[] = sprintf('%s %s %s', $tpl['START_TIME'], $tpl['TO'],
                $tpl['END_TIME']);
        if (!empty($event->location)) {
            $summary[] = $event->getLocation();
        }

        $blog->summary = PHPWS_Text::parseInput('<p class="calendar-post">' . implode('<br />',
                                $summary) . '</p>') . $event->description;
        $blog->approved = 1;

        $days = (int) $_POST['advance_post'];

        $publish = $event->start_time - ($days * 86400);
        if ($publish < time()) {
            $blog->publish_date = time();
        } else {
            $blog->publish_date = & $publish;
        }

        return !PHPWS_Error::logIfError($blog->save());
    }

}

?>