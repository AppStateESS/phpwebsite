<?php

  /**
   * Contains administrative functionality
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


class Calendar_Admin {
    // parent variable
    var $calendar    = NULL;
    //    var $schedule    = NULL;
    var $errors      = NULL;
    var $event       = NULL;


    function main()
    {
        if (!Current_User::allow('calendar')) {
            Current_User::disallow();
            return;
        }

        $title = $content = NULL;
        $message = $this->getMessage();

        $panel = $this->getPanel();

        if (isset($_REQUEST['aop'])) {
            $command = $_REQUEST['aop'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (!$this->checkAuthorized($command)) {
            Current_User::disallow();
        }


        switch ($command) {
        case 'create_event_js':
            $this->calendar->loadSchedule();
            $result = $this->loadEvent();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                PHPWS_Core::errorPage();
            }
            $content = $this->editEventJS();
            Layout::nakedDisplay($content);
            exit();

        case 'create_personal_schedule':
            $panel->setCurrentTab('my_schedule');
            $this->calendar->loadSchedule();
            $title = _('Create Personal Schedule');
            $content = $this->editSchedule(TRUE);
            break;


        case 'create_schedule':
            if (!Current_User::allow('calendar', 'create_schedule')) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $this->calendar->loadSchedule();
            $title = _('Create Schedule');
            $content = $this->editSchedule();
            break;

        case 'delete_schedule':
            $result = $this->calendar->schedule->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $message = _('An error occurred. Please check your logs.');
            } else {
                $message = _('Schedule deleted.');
            }
            $this->sendMessage($message, 'schedules');
            break;

        case 'post_settings':
            $this->saveSettings();
            $this->sendMessage(_('Settings saved'), 'settings');
            break;

        case 'edit_event_js':
            $this->calendar->loadSchedule();
            $result = $this->loadEvent();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                PHPWS_Core::errorPage();
            }
            $content = $this->editEventJS();
            Layout::nakedDisplay($content);
            break;

        case 'edit_schedule':
            if (!Current_User::allow('calendar', 'edit_schedule')) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $this->calendar->loadSchedule();
            $title = _('Update Schedule');
            $content = $this->editSchedule();
            break;

        case 'main':
            if (Current_User::allow('calendar', 'edit_public')) {
                $panel->setCurrentTab('schedules');
                $title = _('Schedules');
                $content = $this->scheduleListing();
            } else {
                $panel->setCurrentTab('my_schedule');
                $content = $this->mySchedule();
                $title = _('My Schedule');
            }
            break;

        case 'my_schedule':
            $panel->setCurrentTab('my_schedule');
            $content = $this->mySchedule();
            $title = _('My Schedule');
            break;

        case 'post_event_js':

            $result = $this->event->postEvent();
            if (!$result) {
                $content = $this->editEventJS();
                Layout::nakedDisplay($content);
            } else {
                $result = $this->event->save();
                $content = javascript('alert', array('content' => _('Event saved successfully.')));
                javascript('close_refresh', array('timeout'=> .5));
                Layout::nakedDisplay($content);
            }

            break;

        case 'post_schedule':
            if ($this->calendar->schedule->post()) {
                $result = $this->calendar->schedule->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->sendMessage(_('There was a problem saving your schedule.'), 'schedules');
                } else {
                    $this->sendMessage(_('Schedule saved successfully.'), 'schedules');
                }
            } else {
                $message = implode('<br />', $this->calendar->schedule->_error);
                $this->editSchedule();
            }
            break;

        case 'schedules':
            $panel->setCurrentTab('schedules');
            $title = _('Schedules');
            $content = $this->scheduleListing();
            break;

        case 'settings':
            $title = _('Settings');
            $content = $this->settings();
            break;
        } // End of admin switch

        $tpl['CONTENT'] = $content;
        $tpl['TITLE']   = $title;
        $tpl['MESSAGE'] = $message;

        $final = PHPWS_Template::process($tpl, 'calendar', 'admin/main.tpl');

        $panel->setContent($final);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }


    /**
     * Checks commands that alter the database for authenticity
     */
    function checkAuthorized($command)
    {
        $this->calendar->loadSchedule();
        if (!($schedule_id = $this->calendar->schedule->id)) {
            $schedule_id = NULL;
        }

        $public = $this->calendar->schedule->public_schedule;

        switch ($command) {
        case 'delete_schedule':
            if (Current_User::authorized('calendar', 'delete_schedule')) {
                return TRUE;
            }
            break;

        case 'post_schedule':
            if ( $public && ( Current_User::authorized('calendar', 'edit_public', $schedule_id) ) ) {
                // user has permission to create/edit public calendars
                return TRUE;
            } elseif (!$public) {
                // a private schedule
                if ( !$schedule_id ) {
                    // a new schedule
                    if ( ( isset($_REQUEST['user_id']) && $_REQUEST['user_id'] == Current_User::getId() ) || 
                         Current_User::authorized('calendar', 'edit_private') ) {
                        // either this is a new personal private schedule or the user is allowed to post private schedules
                        return TRUE;
                    } elseif ( Current_User::authorized('calendar', 'edit_private', $schedule_id) ){
                        // user has permissions to create/edit private schedules
                        return TRUE;
                    }
                }
            }

            break;

        case 'post_event_js':
            $this->loadEvent();

            // An event can be posted if the schedule is public and the current user has rights
            // to edit this public calendar
            if ( $public && ( Current_User::authorized('calendar', 'edit_public', $schedule_id) ) ) {
                return TRUE;
            } 
            // This event can be posted if the schedule is private and it belongs to the current user
            // or the user has permission to edit private calendars
            elseif ( !$public && ( $this->calendar->schedule->user_id == Current_User::getId() ||
                                   Current_User::authorized('calendar', 'edit_private', $schedule_id) ) ) {
                return TRUE;
            }

            break;

        default:
            return TRUE;
        } // end command switch

        return FALSE;
    }

    function loadEvent()
    {
        PHPWS_Core::initModClass('calendar', 'Event.php');
        if (isset($_REQUEST['event_id'])) {
            $this->event = & new Calendar_Event($_REQUEST['event_id']);
        } else {
            $this->event = & new Calendar_Event;
        }
        if ($this->event->_error) {
            return $this->event->_error;
        } else {
            return TRUE;
        }
    }

    function &getPanel()
    {
        $panel = & new PHPWS_Panel('calendar');

        $link = 'index.php?module=calendar';

        $tabs['my_schedule'] = array('title' => _('My Schedule'), 'link' => $link);

        $tabs['events']      = array('title' => _('Events'), 'link' => $link);
        if (Current_User::allow('calendar', 'edit_public') ||
            Current_User::allow('calendar', 'edit_private')) {
            $tabs['schedules']   = array('title' => _('Schedules'), 'link' => $link);
        }

        if (Current_User::allow('calendar', 'settings')) {
            $tabs['settings']    = array('title' => _('Settings'), 'link' => $link);
        }

        $panel->quickSetTabs($tabs);
        return $panel;
    }

    /**
     * Saves the settings posted from the settings page
     */
    function saveSettings()
    {
        PHPWS_Settings::set('calendar', 'info_panel',         $_POST['info_panel']);
        PHPWS_Settings::set('calendar', 'starting_day',       $_POST['starting_day']);
        PHPWS_Settings::set('calendar', 'personal_schedules', $_POST['personal_schedules']);
        PHPWS_Settings::set('calendar', 'hour_format',        $_POST['hour_format']);
        PHPWS_Settings::set('calendar', 'display_mini',       $_POST['display_mini']);
        PHPWS_Settings::save('calendar');
    }

    function sendMessage($message, $command)
    {
        $_SESSION['Calendar_Admin_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=calendar&aop=' . $command);
    }

    function getMessage()
    {
        if (!isset($_SESSION['Calendar_Admin_Message'])) {
            return NULL;
        }

        $message = $_SESSION['Calendar_Admin_Message'];
        unset($_SESSION['Calendar_Admin_Message']);
        return $message;
    }

    function scheduleListing()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('calendar', 'Schedule.php');

        $page_tags['TITLE_LABEL']        = _('Title');
        $page_tags['SUMMARY_LABEL']      = _('Summary');
        $page_tags['PUBLIC_LABEL']       = _('Public');
        $page_tags['DISPLAY_NAME_LABEL'] = _('User');
        $page_tags['ADD_CALENDAR']       = PHPWS_Text::secureLink(_('Create schedule'), 'calendar',
                                                                  array('aop'=>'create_schedule'));
        $page_tags['ADMIN_LABEL']        = _('Options');

        $pager = & new DBPager('calendar_schedule', 'Calendar_Schedule');
        $pager->setModule('calendar');
        $pager->setTemplate('admin/calendars.tpl');
        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');
        $pager->setEmptyMessage(_('No schedules have been created.'));
        $pager->db->addWhere('user_id', 0);
        $pager->db->addWhere('user_id', 'users.id', '=', 'or');
        $pager->db->addColumn('*');
        $pager->db->addColumn('users.display_name');
        $pager->db->addJoin('left', 'calendar_schedule', 'users', 'user_id', 'id');

        $pager->initialize();
 
        $content = $pager->get();
        
        return $content;
    }


    function mySchedule()
    {
        if (!PHPWS_Settings::get('calendar', 'personal_schedules')) {
            return _('Sorry, personal schedules are disabled.');
        }

        $result = $this->calendar->loadSchedule(TRUE);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->sendMessage(_('An error occurred when accessing the schedules.'));
            return NULL;
        } elseif (!$result) {
            $this->sendMessage(_('You currently do not have a personal schedule. Please create one.'), 'create_personal_schedule');
        }

        return $this->calendar->view();
    }


    function editSchedule($personal=FALSE)
    {
        $schedule = &$this->calendar->schedule;

        $form = & new PHPWS_Form;

        // Checks need to be made on new calendars
        // When a calendar is edited, its type is unchanging

        if ($personal) {
            if (!PHPWS_Settings::get('calendar', 'personal_schedules')) {
                return _('Personal schedules are disabled.');
            }

            if (empty($schedule->id)) {
                $schedule->title = Current_User::getDisplayName();
                // Check to see if the user already has a personal
                // calendar
                $db = & new PHPWS_DB('calendar_schedule');
                $db->addWhere('user_id', Current_User::getId());
                $db->addColumn('id');
                $result = $db->select('one');
                if (!empty($result)) {
                    $this->calendar->schedule = & new Calendar_Schedule($result);
                }
                $form->addHidden('user_id', Current_User::getId());
            }
        }

        if (Current_User::allow('calendar', 'edit_public')) {
            $form->addRadio('public', array('0'=>'0','1'=>'1'));
            $form->setMatch('public', $schedule->public_schedule);
            $form->setLabel('public', array(_('Private'), _('Public')));
        } else {
            $form->addHidden('public', 0);
        }

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_schedule');
        $form->addHidden('schedule_id', $schedule->id);


        $form->addText('title', $schedule->title);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('summary', $schedule->summary);
        $form->setLabel('summary', _('Summary'));

        $form->addSubmit(_('Save calendar'));

        $tpl = $form->getTemplate();

        $tpl['PUBLIC_LABEL'] = _('Availability');

        $content = PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_schedule.tpl');

        return $content;
    }

    /**
     * The javascript popup window for creating an event
     */
    function editEventJS()
    {
        $form = & new PHPWS_Form('event_form');

        if ($this->event->id) {
            $form->addHidden('event_id', $this->event->id);
        }

        if (isset($_REQUEST['date']) && !$this->event->id) {
            $this->event->start_time = $_REQUEST['date'];
            $this->event->end_time = $_REQUEST['date'];
        }


        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_event_js');
        $form->addHidden('schedule_id', $this->calendar->schedule->id);
        $form->addText('title', $this->event->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 60);

        $form->addTextArea('summary', $this->event->summary);
        $form->setLabel('summary', _('Summary'));
        $form->useEditor('summary');

        $form->addText('start_date', $this->event->getStartTime('%Y/%m/%d'));
        $form->setLabel('start_date', _('Start time'));
        $form->setExtra('start_date', 'onblur="check_start_date()"');

        $form->addText('end_date', $this->event->getEndTime('%Y/%m/%d'));
        $form->setLabel('end_date', _('End time'));
        $form->setExtra('end_date', 'onblur="check_end_date()" onfocus="check_start_date()"');

        $form->addButton('close', _('Cancel'));
        $form->setExtra('close', 'onclick="window.close()"');

        $this->timeForm('start_time', $this->event->start_time, $form);
        $this->timeForm('end_time', $this->event->end_time, $form);

        $form->setExtra('start_time_hour', 'onchange="check_start_date()"');
        $form->setExtra('end_time_hour', 'onchange="check_end_date()"');

        $event_types[] = 1;
        $event_labels[1] = _('Normal');
        $event_types[] = 2;
        $event_labels[2] = _('All day');
        $event_types[] = 3;
        $event_labels[3] = _('Starts at');
        $event_types[] = 4;
        $event_labels[4] = _('Deadline');

        $form->addRadio('event_type', $event_types);
        $form->setLabel('event_type', $event_labels);
        $form->setExtra('event_type', 'onchange="alter_date(this)"');

        $form->setMatch('event_type', $this->event->event_type);
        $form->addTplTag('EVENT_TYPE_LABEL', _('Event type'));

        $form->addSubmit(_('Save event'));

        $tpl = $form->getTemplate();

        $js_vars['date_name'] = 'start_date';
        $tpl['START_CAL'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'end_date';
        $tpl['END_CAL'] = javascript('js_calendar', $js_vars);

        if (isset($this->event->_error)) {
            $tpl['ERROR'] = implode('<br />', $this->event->_error);
        }

        return PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_event.tpl');
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

    function dateForm($name, $match, &$form) {
        static $month = NULL;
        static $day = NULL;
        static $year = NULL;

        if (!$match) {
            $match = mktime();
        }
        
        if (!$month) {
            $months = $this->calendar->getMonthArray();
        }

        if (!$day) {
            $days = $this->calendar->getDayArray();
        }

        if (!$year) {
            $years = $this->calendar->getYearArray();
        }

        $month_match = date('m', $match);
        $day_match = date('d', $match);
        $year_match = date('Y', $match);

        $form->addSelect($name . '_month', $months);
        $form->setMatch($name . '_month', $month_match);

        $form->addSelect($name . '_day', $days);
        $form->setMatch($name . '_day', $day_match);

        $form->addSelect($name . '_year', $years);
        $form->setMatch($name . '_year', $year_match);
    }

    function settings()
    {
        $info_panel = PHPWS_Settings::get('calendar', 'info_panel');

        $form = & new PHPWS_Form('calendar_settings');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_settings');

        $db = & new PHPWS_DB('calendar_schedule');
        $db->addWhere('public_schedule', 1);
        $db->addColumn('id');
        $db->addColumn('title');
        $db->setIndexBy('id');
        $calendar_list = $db->select('col');
        if (PEAR::isError($calendar_list)) {
            PHPWS_Error::log($calendar_list);
            return _('There was an error when trying to access your calendars. Check your logs.');
        }

        // Using October 2006 because the first day was Sunday
        for($i=0; $i<7; $i++) {
            $days[$i] = strftime('%A', mktime(0,0,0,10,$i+1,2006));
        }

        $mini_select[0] = _('None');
        if (!empty($calendar_list)) {
            $mini_select = array_merge($mini_select, $calendar_list);
        }

        $form->addSelect('info_panel', $mini_select);
        $form->setMatch('info_panel', $info_panel);
        $form->setLabel('info_panel', _('Information panel'));

        $form->addSelect('starting_day', $days);
        $form->setMatch('starting_day', PHPWS_Settings::get('calendar', 'starting_day'));
        $form->setLabel('starting_day', _('Week start'));

        $form->addRadio('personal_schedules', array(0,1));
        $form->setLabel('personal_schedules', array(_('Off'), _('On')));
        $form->setMatch('personal_schedules', PHPWS_Settings::get('calendar', 'personal_schedules'));

        $am = strftime('%p', mktime(10));
        $pm = strftime('%p', mktime(16));

        $form->addSelect('hour_format', array('g'=>"9:00$am - 3:00$pm",
                                              'G'=>"9:00 - 15:00",
                                              'h'=>"09:00$am - 03:00$pm",
                                              'H'=>'09:00 - 15:00'));
        $form->setMatch('hour_format', PHPWS_Settings::get('calendar', 'default_hour_format'));
        $form->setLabel('hour_format', _('Hour format'));

        $form->addRadio('display_mini', array(1 => MINI_CAL_NO_SHOW,
                                              2 => MINI_CAL_SHOW_FRONT,
                                              3 => MINI_CAL_SHOW_ALWAYS));
        $form->setLabel('display_mini', array(1 =>_('Never show'),
                                              2 =>_('Show on front page only'),
                                              3 =>_('Show always')));
        $form->setMatch('display_mini', PHPWS_Settings::get('calendar', 'display_mini'));

        $form->addSubmit(_('Save'));
        
        $template = $form->getTemplate();
        $template['DISPLAY_MINI_LABEL'] = _('Mini Calendar display');
        $template['PERSONAL'] = _('Personal schedules');

        return PHPWS_Template::process($template, 'calendar', 'admin/forms/settings.tpl');

    }

}



?>