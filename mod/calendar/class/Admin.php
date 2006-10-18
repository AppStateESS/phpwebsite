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
        if ($event->id) {
            $this->title = _('Update event');
        } else {
            $this->title = _('Create event');
        }

        $this->content = $this->event_form($event);
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

    /**
     * Creates the edit form for an event
     */
    function event_form(&$event)
    {
        Layout::addStyle('calendar');
        
        // the form id is linked to the check_date javascript
        $form = new PHPWS_Form('event_form');
        if (isset($_REQUEST['js'])) {
            $form->addHidden('js', 1);
        }

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_event');
        $form->addHidden('event_id', $event->id);
        $form->addHidden('sch_id', $event->_schedule->id);

        $form->addText('summary', $event->summary);
        $form->setLabel('summary', _('Summary'));
        $form->setSize('summary', 60);

        $form->addText('location', $event->location);
        $form->setLabel('location', _('Location'));
        $form->setSize('location', 60);

        $form->addText('loc_link', $event->loc_link);
        $form->setLabel('loc_link', _('Location link'));
        $form->setSize('loc_link', 60);

        $form->addTextArea('description', $event->description);
        $form->useEditor('description');
        $form->setLabel('description', _('Description'));

        $form->addText('start_date', $event->getStartTime('%Y/%m/%d'));
        $form->setLabel('start_date', _('Start time'));
        $form->setExtra('start_date', 'onblur="check_start_date()"');

        $form->addText('end_date', $event->getEndTime('%Y/%m/%d'));
        $form->setLabel('end_date', _('End time'));
        $form->setExtra('end_date', 'onblur="check_end_date()" onfocus="check_start_date()"');

        $form->addButton('close', _('Cancel'));
        $form->setExtra('close', 'onclick="window.close()"');

        $event->timeForm('start_time', $event->start_time, $form);
        $event->timeForm('end_time', $event->end_time, $form);

        $form->setExtra('start_time_hour', 'onchange="check_start_date()"');
        $form->setExtra('end_time_hour', 'onchange="check_end_date()"');

        $form->addCheck('all_day', 1);
        $form->setMatch('all_day', $event->all_day);
        $form->setLabel('all_day', _('All day event'));
        $form->setExtra('all_day', 'onchange="alter_date(this)"');

        $form->addCheck('show_busy', 1);
        $form->setMatch('show_busy', $event->show_busy);
        $form->setLabel('show_busy', _('Show busy'));

        /**
         * Repeat form elements
         */

        $form->addCheck('repeat_event', 1);
        $form->setLabel('repeat_event', _('Make a repeating event'));

        $form->addText('end_repeat_date', $event->getEndRepeat('%Y/%m/%d'));
        $form->setLabel('end_repeat_date', _('Repeat event until:'));

        $modes = array('daily',
                       'weekly',
                       'monthly',
                       'yearly',
                       'every');


        $modes_label = array(_('Daily'),
                             _('Weekly'),
                             _('Monthly'),
                             _('Yearly'),
                             _('Every'));

        $form->addRadio('repeat_mode', $modes);
        $form->setLabel('repeat_mode', $modes_label);

        $weekdays = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7);

        $weekday_labels = array(1=>strftime('%A', mktime(0,0,0,1,5,1970)),
                                2=>strftime('%A', mktime(0,0,0,1,6,1970)),
                                3=>strftime('%A', mktime(0,0,0,1,7,1970)),
                                4=>strftime('%A', mktime(0,0,0,1,8,1970)),
                                5=>strftime('%A', mktime(0,0,0,1,9,1970)),
                                6=>strftime('%A', mktime(0,0,0,1,10,1970)),
                                7=>strftime('%A', mktime(0,0,0,1,11,1970))
                                );

        $form->addCheck('weekday_repeat', $weekdays);
        $form->setLabel('weekday_repeat', $weekday_labels);

        $monthly = array('begin' => _('Beginning of each month'),
                         'end'   => _('End of each month'),
                         'start' => _('Every month on start date')
                         );

        $form->addSelect('monthly_repeat', $monthly);

        $every_repeat_week = array(1   => _('1st'),
                                   2   => _('2nd'),
                                   3   => _('3rd'),
                                   4   => _('4th'),
                                   5   => _('Last')
                                   );

        $frequency = array('every_month' => _('Every month'),
                           1 => strftime('%B', mktime(0,0,0,1,1,1970)),
                           2 => strftime('%B', mktime(0,0,0,2,1,1970)),
                           3 => strftime('%B', mktime(0,0,0,3,1,1970)),
                           4 => strftime('%B', mktime(0,0,0,4,1,1970)),
                           5 => strftime('%B', mktime(0,0,0,5,1,1970)),
                           6 => strftime('%B', mktime(0,0,0,6,1,1970)),
                           7 => strftime('%B', mktime(0,0,0,7,1,1970)),
                           8 => strftime('%B', mktime(0,0,0,8,1,1970)),
                           9 => strftime('%B', mktime(0,0,0,9,1,1970)),
                           10 => strftime('%B', mktime(0,0,0,10,1,1970)),
                           11 => strftime('%B', mktime(0,0,0,11,1,1970)),
                           12 => strftime('%B', mktime(0,0,0,12,1,1970)));

        $form->addSelect('every_repeat_number', $every_repeat_week);
        $form->addSelect('every_repeat_weekday', $weekday_labels);
        $form->addSelect('every_repeat_frequency', $frequency);

        /* set repeat form matches */

        if (!empty($event->repeat_type)) {
            $repeat_info = explode(':', $event->repeat_type);
            $repeat_mode_match = $repeat_info[0];
            if (isset($repeat_info[1])) {
                $repeat_vars = explode(';', $repeat_info[1]);
            }

            $form->setMatch('repeat_mode', $repeat_mode_match);

            switch($repeat_mode_match) {
            case 'weekly':
                $form->setMatch('weekday_repeat', $repeat_vars);
                break;

            case 'monthly':
                $form->setMatch('monthly_repeat', $repeat_vars[0]);
                break;

            case 'every':
                $form->setMatch('every_repeat_number', $repeat_vars[0]);
                $form->setMatch('every_repeat_weekday', $repeat_vars[1]);
                $form->setMatch('every_repeat_frequency', $repeat_vars[2]);
                break;
            }

            $form->setMatch('repeat_event', 1);
        }


        if ($event->pid) {
            $form->addHidden('pid', $event->pid);
            // This is a repeat copy, if saved it removes it from the copy list
            $form->addSubmit('save', _('Save and remove repeat'));
            $form->setExtra('save', sprintf('onclick="return confirm(\'%s\')"',
                                            _('Remove event from repeat list?')) );
        } elseif ($event->id && $event->repeat_type) {
            // This is event is a source repeating event

            // Save this 
            // Not sure if coding this portion. commenting for now
            // $form->addSubmit('save_source', _('Save this event only'));
            $form->addSubmit('save_copy', _('Save and apply to repeats'));
            $form->setExtra('save_copy', sprintf('onclick="return confirm(\'%s\')"',
                                            _('Apply changes to repeats?')) );
        } else {
            // this is a non-repeating event
            $form->addSubmit('save', _('Save event'));
        }

        $tpl = $form->getTemplate();

        $js_vars['date_name'] = 'start_date';
        $tpl['START_CAL'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'end_date';
        $tpl['END_CAL'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'end_repeat_date';
        $tpl['END_REPEAT'] = javascript('js_calendar', $js_vars);


        if (isset($event->_error)) {
            $tpl['ERROR'] = implode('<br />', $event->_error);
        }

        if ($event->pid) {
            $linkvar['aop']      = 'edit_event';
            $linkvar['sch_id']   = $event->_schedule->id;
            $linkvar['event_id'] = $event->pid;
            if (javascriptEnabled()) {
                $linkvar['js'] = 1;
            }

            $source_link = PHPWS_Text::moduleLink(_('Click here if you would prefer to edit the source event.'), 'calendar', $linkvar);
            $tpl['REPEAT_WARNING'] = _('This is a repeat of another event.') . '<br />' . $source_link;
        }

        javascript('modules/calendar/edit_event');
        javascript('modules/calendar/check_date');
        $tpl['EVENT_TAB'] = _('Event');
        $tpl['REPEAT_TAB'] = _('Repeat');
        return PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_event.tpl');
    }


    function &getPanel()
    {
        $panel = new PHPWS_Panel('calendar');


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

        case 'delete_event':
            $event = $this->calendar->schedule->loadEvent();
            $result = $event->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            PHPWS_Core::goBack();
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

        case 'post_schedule':
            if (!$this->checkAuthorization('edit_schedule', $_POST['sch_id'])) {
                Current_User::disallow();
            }
            $this->postSchedule();
            break;

        case 'post_settings':
            $this->postSettings();
            $this->message = _('Settings saved');
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
        }

        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE']   = $this->title;

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

    /**
     * Checks the legitimacy of the event and saves the results
     */
    function postEvent()
    {
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
                $result = $this->saveRepeat($event);
                if (PEAR::isError($result)) {
                    if (PHPWS_Calendar::isJS()) {
                        PHPWS_Error::log($result);
                        $this->sendMessage(_('An error occurred when trying to repeat an event.', null, false));
                        javascript('close_refresh');
                        Layout::nakedDisplay();
                        exit();
                    } else {
                        $this->sendMessage(_('An error occurred when trying to repeat an event.', 'schedules'));
                    }
                }

                if(PHPWS_Calendar::isJS()) {
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('Event saved.'), 'schedules');
                }
            }
        } else {
            $this->editEvent($event);
        }
    }

    function postSettings()
    {
        PHPWS_Settings::set('calendar', 'starting_day', (int)$_POST['starting_day']);
        PHPWS_Settings::save('calendar');
        PHPWS_Cache::clearCache();
    }

    /**
     * Saves a repeated event
     */
    function saveRepeat(&$event)
    {

        // if this event has a parent id, don't try and save repeats
        if ($event->pid) {
            return true;
        }

        // This event is not repeating
        if (empty($event->repeat_type)) {
            // Previously, the event repeated, remove the copies
            $result = $event->clearRepeats();
            if (PEAR::isError($result)) {
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
        if (PEAR::isError($result)) {
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

        if (PEAR::isError($result)) {
            return $result;
        } else {
            return true;
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

    function repeatDaily(&$event)
    {
        PHPWS_Core::requireConfig('calendar');

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        while($time_unit <= $event->end_repeat) {
            $copy_event->id = 0;

            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar', 'Calendar_Admin::repeatDaily');
            }
            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $time_unit += 86400;
            $result = $copy_event->save();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    function repeatEvent($event)
    {
        if (!$event->id) {
            $this->content = _('This event does not exist.');
            return;
        }

        $this->title = sprintf(_('Repeat event - %s'), $event->summary);
        if (@$_REQUEST['js']) {
            $js = true;
        } else {
            $js = false;
        }
        $this->content = $event->repeat($js);
    }

    function repeatEvery(&$event)
    {

        if (!isset($_POST['monthly_repeat'])) {
            return false;
        }

        $max_count = 0;

        $every_repeat_number = &$_POST['every_repeat_number'];
        $every_repeat_weekday =  &$_POST['every_repeat_weekday'];
        $every_repeat_frequency = &$_POST['every_repeat_frequency'];

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        $repeat_days = &$_POST['weekday_repeat'];

        $weekday_count = 0;

        while ($time_unit < $event->end_repeat) {
            $copy_event->id = 0;

            // First check if we are in the correct month or if the repeat is in every month
            if ($every_repeat_frequency == 'every_month' || $every_repeat_frequency == (int)strftime('%m', $time_unit)) {

                // next check if we are in the correct weekday
                $current_weekday = (int)strftime('%u', $time_unit);

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
                    if ( $weekday_count == $every_repeat_number ||
                         ( $every_repeat_number == 5 && $weekday_count == 4 && ( ($current_day + 7) > date('t', $time_unit) ) ) ) {
                        $weekday_found = true;
                        $max_count++;
                        
                        if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                            return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar', 'Calendar_Admin::repeatWeekly');
                        }

                        $copy_event->start_time = $time_unit;
                        $copy_event->end_time = $time_unit + $time_diff;
                        $result = $copy_event->save();
                        if (PEAR::isError($result)) {
                            return $result;
                        }
                    }

                }
            }


            $time_unit += 86400;
        }
        //        exit('wtf');

    }


    function repeatMonthly(&$event)
    {
        if (!isset($_POST['monthly_repeat'])) {
            return false;
        }

        $max_count = 0;

        $c_hour  = (int)strftime('%H', $event->start_time);
        $c_min   = (int)strftime('%M', $event->start_time);
        $c_month = (int)strftime('%m', $event->start_time);
        $c_day   = (int)strftime('%d', $event->start_time);
        $c_year  = (int)strftime('%Y', $event->start_time);

        $time_diff = $event->end_time - $event->start_time;
        $copy_event = $event->repeatClone();
        // start count on month ahead
        $ts_count = mktime($c_hour, $c_min, 0, $c_month + 1, $c_day, $c_year);

        while ($ts_count <= $event->end_repeat) {
            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar', 'Calendar_Admin::repeatMonthly');
            }

            $copy_event->id = 0;            

            $c_hour = (int)strftime('%H', $ts_count);
            $c_min = (int)strftime('%M', $ts_count);
            $ts_month = $c_month = (int)strftime('%m', $ts_count);
            $ts_day = $c_day = (int)strftime('%d', $ts_count);
            $c_year = (int)strftime('%Y', $ts_count);

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
            if (PEAR::isError($result)) {
                return $result;
            }

            $ts_count = mktime($c_hour, $c_min, 0, $c_month + 1, $c_day, $c_year);
        }
        return true;
    }


    function repeatWeekly(&$event)
    {
        if (!isset($_POST['weekday_repeat']) || !is_array($_POST['weekday_repeat'])) {
            $this->message = _('You must choose which weekdays to repeat.');
            return false;
        }

        $time_unit = $event->start_time + 86400;

        $copy_event = $event->repeatClone();
        $time_diff = $event->end_time - $event->start_time;

        $max_count = 0;
        $repeat_days = &$_POST['weekday_repeat'];

        while($time_unit <= $event->end_repeat) {
            if (!in_array(strftime('%u', $time_unit), $repeat_days)) {
                $time_unit += 86400;
                continue;
            }
            $copy_event->id = 0;

            $max_count++;
            if ($max_count > CALENDAR_MAXIMUM_REPEATS) {
                return PHPWS_Error::get(CAL_REPEAT_LIMIT_PASSED, 'calendar', 'Calendar_Admin::repeatWeekly');
            }
            $copy_event->start_time = $time_unit;
            $copy_event->end_time = $time_unit + $time_diff;
            $result = $copy_event->save();
            if (PEAR::isError($result)) {
                return $result;
            }
            $time_unit += 86400;
        }
        return TRUE;
    }


    function repeatYearly(&$event)
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
        $page_tags['DESCRIPTION_LABEL']  = _('Description');
        $page_tags['PUBLIC_LABEL']       = _('Public');
        $page_tags['DISPLAY_NAME_LABEL'] = _('User');
        $page_tags['AVAILABILITY_LABEL'] = _('Availability');

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

        $pager = new DBPager('calendar_schedule', 'Calendar_Schedule');
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

    function settings()
    {
        $form = new PHPWS_Form('calendar_settings');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('allow_submissions', 1);
        $form->setMatch('allow_submissions', PHPWS_Settings::get('calendar', 'allow_submissions'));
        $form->setLabel('allow_submissions', _('Allow public event submissions'));

        $start_days = array(0,1);
        $start_days_label[0] = strftime('%A', mktime(0,0,0,1,4,1970));
        $start_days_label[1] = strftime('%A', mktime(0,0,0,1,5,1970));
        $form->addRadio('starting_day', $start_days);
        $form->setLabel('starting_day', $start_days_label);
        $form->setMatch('starting_day', PHPWS_Settings::get('calendar', 'starting_day'));

        $form->addCheck('personal_schedules', 1);
        $form->setLabel('personal_schedules', _('Allow personal schedules'));
        $form->setMatch('personal_schedules', PHPWS_Settings::get('calendar', 'personal_schedule'));

        $form->addSubmit(_('Save settings'));
        $tpl = $form->getTemplate();

        $tpl['START_LABEL'] = _('Week start day');

        $this->content = PHPWS_Template::process($tpl, 'calendar', 'admin/settings.tpl');
        $this->title   = _('Calendar settings');
    }

}

?>