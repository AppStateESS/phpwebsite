<?php

  /**
   * Contains administrative functionality
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_Admin {
    /**
     * @var pointer to the parent object
     */
    var $calendar = null;
    var $title   = null;
    var $content = null;
    var $message = null;


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

    function editSchedule($schedule)
    {
        if ($schedule->id) {
            $this->title = _('Update schedule');
        } else {
            $this->title = _('Create schedule');
        }

        $this->content = $schedule->form();
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


    function &loadSchedule()
    {
        PHPWS_Core::initModClass('calendar', 'Schedule.php');

        if (!empty($_REQUEST['schedule_id'])) {
            $schedule = & new Calendar_Schedule((int)$_REQUEST['schedule_id']);
        } else {
            $schedule = & new Calendar_Schedule;
        }

        return $schedule;
    }

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
            $schedule = $this->loadSchedule();
            $event = $schedule->loadEvent();
            $this->editEvent($event);
            break;

        case 'create_schedule':
            if (!Current_User::allow('calendar', 'create_schedule')) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $schedule = $this->loadSchedule();
            $this->editSchedule($schedule);
            break;

        case 'delete_schedule':
            $schedule = $this->loadSchedule();
            $schedule->delete();
            $this->sendMessage(_('Schedule deleted.'), 'schedules');
            break;

        case 'edit_schedule':
            if (empty($_REQUEST['schedule_id'])) {
                PHPWS_Core::errorPage('404');
            }

            if (!Current_User::allow('calendar', 'edit_schedule', (int)$_REQUEST['schedule_id'])) {
                Current_User::disallow();
            }
            $panel->setCurrentTab('schedules');
            $schedule = $this->loadSchedule();
            $this->editSchedule($schedule);
            break;


        case 'my_schedule':
            $panel->setCurrentTab('my_schedule');
            $this->mySchedule();
            break;

        case 'post_schedule':
            if (!$this->checkAuthorization('edit_schedule', $_POST['schedule_id'])) {
                Current_User::disallow();
            }
            $this->postSchedule();
            break;

        case 'schedules':
            $this->scheduleListing();
            break;
        }

        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE']   = $this->title;
        $tpl['MESSAGE'] = $this->message;

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

    function postSchedule()
    {
        $schedule = $this->loadSchedule();
        if ($schedule->post()) {
            $result = $schedule->save();
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
                    $this->sendMessage(_('Schedule created.'), null, false);
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->sendMessage(_('Schedule created.'), 'schedules');
                }
            }
        } else {
            $this->message = $schedule->_error;
            $this->editSchedule($schedule);
        }
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