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
    var $calendar = null;
    var $title    = null;
    var $content  = null;
    var $message  = null;


    function Calendar_Admin()
    {
        if (!isset($_SESSION['Calendar_Admin_Message'])) {
            return NULL;
        }

        $this->message = $_SESSION['Calendar_Admin_Message'];
        unset($_SESSION['Calendar_Admin_Message']);
    }


    function checkAuthorization($command, $id)
    {
        if (empty($id)) {
            return Current_User::authorized('calendar', $command);
        } else {
            return Current_User::authorized('calendar', $command, $id);
        }
    }

    function editEvent($event)
    {
        if (!$event->id) {
            $event->end_time = $this->calendar->current_date;
            $event->start_time = $this->calendar->current_date;
        }

        if ($event->id) {
            $this->title = _('Update event');
        } else {
            $this->title = _('Create event');
        }

        $this->content = $event->form();
    }

    function editSchedule()
    {
        if ($this->calendar->schedule->id) {
            $this->title = _('Update schedule');
        } else {
            $this->title = _('Create schedule');
        }

        $this->content = $this->calendar->schedule->form();
    }

    function &getPanel()
    {
        $panel = & new PHPWS_Panel('calendar');


        $vars['aop'] = 'schedules';
        $tabs['schedules'] = array('title' => _('Schedules'),
                                   'link' => PHPWS_Text::linkAddress('calendar', $vars));

        $vars['aop'] = 'settings';                                   
        if (Current_User::allow('calendar', 'settings')) {
            $tabs['settings']    = array('title' => _('Settings'),
                                         'link' => PHPWS_Text::linkAddress('calendar', $vars));
        }

        $panel->quickSetTabs($tabs);
        return $panel;
    }

    /**
     * routes administrative commands
     */
    function main()
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
        case 'create_event':
            $panel->setCurrentTab('schedules');
            $event = $this->calendar->schedule->loadEvent();
            $this->editEvent($event);
            break;

        case 'create_schedule':
            if (!Current_User::allow('calendar', 'create_schedule')) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $this->editSchedule();
            break;

        case 'delete_schedule':
            $this->calendar->schedule->delete();
            $this->sendMessage(_('Schedule deleted.'), 'schedules');
            break;

        case 'edit_event':
            $panel->setCurrentTab('schedules');
            $event = $this->calendar->schedule->loadEvent();
            $this->editEvent($event);
            break;

        case 'edit_schedule':
            if (empty($_REQUEST['sch_id'])) {
                PHPWS_Core::errorPage('404');
            }

            if (!Current_User::allow('calendar', 'edit_schedule', (int)$_REQUEST['sch_id'])) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $this->editSchedule();
            break;

        case 'make_default_public':
            if (Current_User::isUnrestricted('calendar')) {
                PHPWS_Settings::set('calendar', 'public_schedule', (int)$_REQUEST['sch_id']);
                PHPWS_Settings::save('calendar');
                $this->message =_('Default public schedule set.');
            }
            $this->scheduleListing();
            break;

        case 'my_schedule':
            $panel->setCurrentTab('my_schedule');
            $this->mySchedule();
            break;

        case 'post_event':
            if (!$this->checkAuthorization('edit_schedule', $_POST['sch_id'])) {
                Current_User::disallow();
            }
            $this->postEvent();
            break;

        case 'post_repeat':
            if (!$this->checkAuthorization('edit_schedule', $_POST['sch_id'])) {
                Current_User::disallow();
            }
            $this->postRepeat();
            break;

        case 'post_schedule':
            if (!$this->checkAuthorization('edit_schedule', $_POST['sch_id'])) {
                Current_User::disallow();
            }
            $this->postSchedule();
            break;

        case 'repeat_event':
            $panel->setCurrentTab('schedules');
            $event = $this->calendar->schedule->loadEvent();
            $this->repeatEvent($event);
            break;

        case 'schedules':
            $this->scheduleListing();
            break;

        case 'settings':

            break;
        }

        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE']   = $this->title;

        if (is_array($this->message)) {
            $tpl['MESSAGE'] = implode('<br />', $this->message);
        } else {
            $tpl['MESSAGE'] = $this->message;
        } 


        $final = PHPWS_Template::process($tpl, 'calendar', 'admin/main.tpl');

        if (PHPWS_Calendar::isJS()) {
            Layout::nakedDisplay($final);
        } else {
            $panel->setContent($final);
            Layout::add(PHPWS_ControlPanel::display($panel->display()));
        }

    }

    function mySchedule()
    {
        echo 'my schedule needs work or deletion';
        return;
        //        $this->title = _('My Schedule');
        if (!PHPWS_Settings::get('calendar', 'personal_schedules')) {
            return _('Sorry, personal schedules are disabled.');
        }

        $schedule = Calendar_Schedule::getCurrentUserSchedule();

        if (PEAR::isError($schedule)) {
            PHPWS_Error::log($schedule);
            $this->sendMessage(_('An error occurred when accessing the schedules.'));
            return NULL;
        } elseif (!$schedule) {
            $this->sendMessage(_('You currently do not have a personal schedule. Please create one.'), 'create_personal_schedule');
        }

        $this->content = $schedule->view();
    }

    /**
     * Checks the legitimacy of the event and saves the results
     */
    function postEvent()
    {
        $event = $this->calendar->schedule->loadEvent();
        
        if ($event->post()) {
            $result = $event->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                if(PHPWS_Calendar::isJS()) {
                    $this->sendMessage(_('An error occurred when saving your event.'), null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('An error occurred when saving your event.'), 'schedules');
                }

            } else {
                if(PHPWS_Calendar::isJS()) {
                    $this->sendMessage(_('Event saved.'), null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('Event saved.'), 'schedules');
                }
            }
        } else {
            $this->message = $event->_error;
            $this->editEvent($event);
        }
        
    }


    /**
     * Repeats a posted event
     */
    function postRepeat()
    {
        $event = $this->calendar->schedule->loadEvent();
        $edate = strtotime($_REQUEST['end_repeat_date']);

        $unix_date = mktime('23', '59', '59', 
                            strftime('%m', $edate), strftime('%d', $edate), strftime('%Y', $edate));

        if ($unix_date <= $event->start_time) {
            $this->message = _('The date to repeat until must be greater than the event\'s start date.');
            return FALSE;
        }

        if (!isset($_REQUEST['repeat_mode'])) {
            $this->message = _('You must choose a repeat mode.');
            return FALSE;
        }

        switch ($_REQUEST['repeat_mode']) {
        case 'daily':
            $result = $this->repeatDaily($event, $unix_date);
            break;

        case 'weekly':
            $result = $this->repeatWeekly($event, $unix_date);
            break;

        case 'monthly':
            $result = $this->repeatMonthly($event, $unix_date);
            break;

        case 'yearly':
            $result = $this->repeatYearly($event, $unix_date);
            break;

        case 'every':
            $result = $this->repeatEvery($event, $unix_date);
            break;
        }

    }

    function postSchedule()
    {
        if ($this->calendar->schedule->post()) {
            $result = $this->calendar->schedule->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                if(PHPWS_Calendar::isJS()) {
                    $this->sendMessage(_('An error occurred when saving your schedule.'), null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('An error occurred when saving your schedule.'), 'schedules');
                }
            } else {
                if(PHPWS_Calendar::isJS()) {
                    $this->sendMessage(_('Schedule saved.'), null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('Schedule saved.'), 'schedules');
                }
            }
        } else {
            $this->message = $this->calendar->schedule->_error;
            $this->editSchedule();
        }
    }

    function repeatDaily(&$event, $until_date)
    {
        $time_unit = $event->start_time + 86400;

        $copy_event = $event;

        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;

        while($time_unit <= $until_date) {
            $copy_event->id = 0;
            $copy_event->key_id = 0;
            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get();
            }
            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $time_unit += 86400;
            $result = $copy_event->save();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return TRUE;
    }

    function repeatEvent($event)
    {
        if (!$event->id) {
            $this->content = _('This event does not exist.');
            return;
        }

        $this->title = sprintf(_('Repeat event - %s'), $event->title);
        if (@$_REQUEST['js']) {
            $js = true;
        } else {
            $js = false;
        }
        $this->content = $event->repeat($js);
    }

    function repeatEvery(&$event, $until_date)
    {

    }


    function repeatMonthly(&$event, $until_date)
    {

    }


    function repeatWeekly(&$event, $until_date)
    {
        if (!isset($_REQUEST['weekday_repeat']) || !is_array($_REQUEST['weekday_repeat'])) {
            $this->message = _('You must choose which weekdays to repeat.');
        }
        $time_unit = $event->start_time + 86400;

        $copy_event = $event;

        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;

        $repeat_days = &$_REQUEST['weekday_repeat'];

        while($time_unit <= $until_date) {
            if (!in_array(strftime('%u', $time_unit), $repeat_days)) {
                continue;
            }
            $copy_event->id = 0;
            $copy_event->key_id = 0;
            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get();
            }
            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $time_unit += 86400;
            $result = $copy_event->save();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return TRUE;
    }


    function repeatYearly(&$event, $until_date)
    {

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

    function scheduleListing()
    {
        $this->title = _('Schedules');

        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('calendar', 'Schedule.php');

        $page_tags['TITLE_LABEL']        = _('Title');
        $page_tags['SUMMARY_LABEL']      = _('Summary');
        $page_tags['PUBLIC_LABEL']       = _('Public');
        $page_tags['DISPLAY_NAME_LABEL'] = _('User');

        $vars = array('aop'=>'create_schedule');
        $label = _('Create schedule');

        if (javascriptEnabled()) {
            $vars['js'] = 1;
            $js_vars['address'] = PHPWS_Text::linkAddress('calendar', $vars);
            $js_vars['label']   = $label;
            $js_vars['width']   = 640;
            $js_vars['height']  = 600;
            $page_tags['ADD_CALENDAR']       = javascript('open_window', $js_vars);
        } else {
            $page_tags['ADD_CALENDAR'] = PHPWS_Text::secureLink($label, 'calendar', $vars);
        }
            
        $page_tags['ADMIN_LABEL']        = _('Options');

        $pager = & new DBPager('calendar_schedule', 'Calendar_Schedule');
        $pager->setModule('calendar');
        $pager->setTemplate('admin/schedules.tpl');
        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');
        $pager->setEmptyMessage(_('No schedules have been created.'));
        
        $pager->db->addWhere('user_id', 0);
        $pager->db->addWhere('user_id', 'users.id', '=', 'or');
        
        $pager->db->addColumn('*');
        $pager->db->addColumn('users.display_name');
        $pager->db->addJoin('left', 'calendar_schedule', 'users', 'user_id', 'id');

        $pager->initialize();
 
        $this->content = $pager->get();
    }

    function sendMessage($message, $command=null, $route=true)
    {
        $_SESSION['Calendar_Admin_Message'] = $message;
        if ($route && !empty($command)) {
            PHPWS_Core::reroute('index.php?module=calendar&aop=' . $command);
        }
    }

}

?>