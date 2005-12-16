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
    var $schedule    = NULL;
    var $errors      = NULL;
    var $event       = NULL;


    function main()
    {
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

        switch ($command) {
        case 'main':
        case 'my_calendar':
            $content = $this->myCalendar();
            $title = _('My Calendar');
            break;

        case 'create_schedule':
            if (!Current_User::allow('calendar', 'create_schedule')) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('calendars');
            $this->calendar->loadSchedule();
            $title = _('Create Calendar');
            $content = $this->editSchedule();
            break;

        case 'post_schedule':
            $this->calendar->loadSchedule();
            $this->postSchedule();
            if (!empty($this->errors)) {
                $message = implode('<br />', $this->errors);
                $this->editSchedule();
            } else {
                $result = $this->calendar->schedule->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->sendMessage(_('There was a problem saving your calendar.', 'calendars'));
                } else {
                    $this->sendMessage(_('Calendar saved successfully.', 'calendars'));
                }
            }
            break;

        case 'events':

            break;

        case 'calendars':
            $title = _('Calendars');
            $content = $this->calendarListing();
            break;

        case 'create_event_js':
            $result = $this->loadEvent();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                PHPWS_Core::errorPage();
            }
            $content = $this->createEventJS();
            Layout::nakedDisplay($content);
            exit();
        }

        $tpl['CONTENT'] = $content;
        $tpl['TITLE']   = $title;
        $tpl['MESSAGE'] = $message;

        $final = PHPWS_Template::process($tpl, 'calendar', 'admin/main.tpl');

        $panel->setContent($final);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
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

        if ( PHPWS_Settings::get('calendar', 'personal_calendars') ||
             Current_User::allow('calendar', 'edit_calendars') ) {
            $tabs['my_calendar'] = array('title' => _('My Calendar'), 'link' => $link);
        }
        $tabs['events']      = array('title' => _('Events'), 'link' => $link);
        $tabs['calendars']   = array('title' => _('Calendars'), 'link' => $link);
        $tabs['settings']    = array('title' => _('Settings'), 'link' => $link);

        $panel->quickSetTabs($tabs);
        return $panel;
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

    function calendarListing()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('calendar', 'Schedule.php');

        $page_tags['TITLE_LABEL']   = _('Title');
        $page_tags['SUMMARY_LABEL'] = _('Summary');
        $page_tags['PUBLIC_LABEL']  = _('Public');
        $page_tags['DISPLAY_NAME_LABEL']    = _('User');

        $pager = & new DBPager('calendar_schedule', 'Calendar_Schedule');
        $pager->setModule('calendar');
        $pager->setTemplate('admin/calendars.tpl');
        $pager->addPageTags($page_tags);
        $pager->initialize();

        $content = $pager->get();
        return $content;
    }


    function myCalendar()
    {
        $this->calendar->loadSchedule();

        if (!PHPWS_Settings::get('calendar', 'personal_calendars')) {
            return array('title' => _('Sorry'),
                         'content' => _('Personal calendars are disabled.'));
        }

        PHPWS_Core::initModClass('calendar', 'Schedule.php');
        $db = & new PHPWS_DB('calendar_schedule');
        $db->addWhere('user_id', Current_User::getId());

        $result = $db->loadObject($this->calendar->schedule);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->sendMessage(_('An error occurred when accessing the calendars.'));
            return NULL;
        } elseif (!$result) {
            $this->sendMessage(_('Currently there aren\'t any calendars. Please make one.'), 'create_schedule');
        }

        $this->calendar->loadView();
        return $this->calendar->view->day();
    }


    function editSchedule()
    {
        $form = & new PHPWS_Form;

        // Checks need to be made on new calendars
        // When a calendar is editted, its type is unchanging
        if (empty($this->calendar->schedule->id)) {
            $this->calendar->schedule->title = Current_User::getDisplayName();
            // Check to see if the user already has a personal
            // calendar
            $db = & new PHPWS_DB('calendar_schedule');
            $db->addWhere('user_id', Current_User::getId());
            $result = $db->select();
            if (!empty($result)) {
                $has_personal_calendar = TRUE;
            } else {
                $has_personal_calendar = FALSE;
            }

            $form->addTplTag('SCHEDULE_TYPE_LABEL', _('Calendar Type'));

            if (PHPWS_Settings::get('calendar', 'personal_calendars')) {
                // User can create new calendars as well as their own
                // personal calendar
                if (Current_User::allow('calendar', 'create_schedule')) {
                    $form->addRadio('schedule_type', array('personal', 'other'));
                    $form->setMatch('schedule_type', 'personal');
                    $form->setLabel('schedule_type', array('personal'=>_('Personal'), 'other'=>_('Other')));
                    $form->addHidden('user_id', Current_User::getId());
                } else {
                    // The user already has a personal calendar and they can't create new ones
                    if ($has_personal_calendar) {
                        return _('You already have a personal calendar and you do not have rights to create a new one.');
                    }
                    // User can only create a personal calendar
                    $form->addHidden('schedule_type', 'personal');
                    $form->addHidden('user_id', Current_User::getId());
                }
            } elseif (!Current_User::allow('calendar', 'create_schedule')){
                return _('Personal calendars are disabled and you do not have the ability to create new public calendars.');
            } else {
                $form->addHidden('schedule_type', 'other');
            }
        }  elseif ($this->calendar->schedule->user_id) {
            $form->addHidden('user_id', $this->calendar->schedule->user_id);
        } else {
            $form->addHidden('user_id', 0);
        }

        $form->addCheck('public', 1);
        $form->setMatch('public', $this->calendar->schedule->public);

        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_schedule');
        $form->addHidden('schedule_id', $this->calendar->schedule->id);


        $form->addText('title', $this->calendar->schedule->title);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('summary', $this->calendar->schedule->summary);
        $form->setLabel('summary', _('Summary'));

        $form->addSubmit(_('Save calendar'));

        $tpl = $form->getTemplate();

        $content = PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_schedule.tpl');

        return $content;
    }

    function postSchedule()
    {
        if (empty($_POST['title'])) {
            $this->errors[] = _('You must give your calendar a title.');
        } else {
            $this->calendar->schedule->setTitle($_POST['title']);
        }

        $this->calendar->schedule->setSummary($_POST['summary']);

        if (isset($_POST['schedule_type'])) {
            if ($_POST['schedule_type'] == 'personal') {
                $this->calendar->schedule->setUserID($_POST['user_id']);
            } else {
                $this->calendar->schedule->user_id = 0;
            }
        } else {
            $this->calendar->schedule->setUserID($_POST['user_id']);
        }

        if (isset($_POST['public'])) {
            $this->calendar->schedule->public = 1;
        } else {
            $this->calendar->schedule->public = 0;
        }

        return TRUE;
    }

    function createEventJS()
    {
        $form = & new PHPWS_Form('event-form');
        $form->addHidden('module', 'calendar');
        $form->addHidden('aop', 'post_event');
        // is this needed?
        //        $form->addHidden('schedule_id', $this->calendar->schedule->id);
        $form->addText('title', $this->event->title);
        $form->setLabel('title', _('Title'));

        $form->addText('summary', $this->event->getSummary());
        $form->setLabel('summary', _('Summary'));

        $this->dateForm('start', $this->event->start_time, $form);

        $tpl = $form->getTemplate();
        test($tpl);
        return PHPWS_Template::process($tpl, 'calendar', 'admin/forms/edit_event.tpl');
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
}



?>