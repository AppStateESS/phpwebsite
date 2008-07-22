<?php

/**
 * The administrative interface for Checkin
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('checkin', 'Checkin.php');

define('CO_FT_LAST_NAME',  1);
define('CO_FT_REASON',     2);


class Checkin_Admin extends Checkin {
    var $panel           = null;
    var $use_panel       = true;
    var $use_sidebar     = true;
    var $current_staff   = null;
    var $current_visitor = null;

    function Checkin_Admin()
    {
        $this->loadPanel();
    }

    function process()
    {
        if (!Current_User::allow('checkin')) {
            Current_User::disallow();
        }

        if (isset($_REQUEST['aop'])) {
            if ($_REQUEST['aop'] == 'switch') {
                if (Current_User::allow('checkin', 'settings')) {
                    $cmd = 'settings';
                } elseif (Current_User::allow('checkin', 'assign_visitors')) {
                    $cmd = 'assign';
                } else {
                    $cmd = 'waiting';
                }
            } else {
                $cmd = $_REQUEST['aop'];
            }
        } elseif ($_REQUEST['tab']) {
            $cmd = $_REQUEST['tab'];
        } else {
            PHPWS_Core::errorPage('404');
        }

        $js = false;

        switch ($cmd) {

        case 'finish_meeting':
            $this->finishMeeting();
            PHPWS_Core::goBack();
            break;

        case 'start_meeting':
            $this->startMeeting();
            PHPWS_Core::goBack();
            break;

        case 'unavailable':
            $this->unavailable();
            PHPWS_Core::goBack();
            break;

        case 'available':
            $this->available();
            PHPWS_Core::goBack();
            break;


        case 'report':
            $this->report(isset($_GET['print']));
            $js = isset($_GET['print']);
            break;

        case 'reassign':
            // Called via ajax
            if (Current_User::authorized('checkin', 'assign_visitors')) {
                if (isset($_GET['staff_id']) && $_GET['staff_id'] >= 0  && isset($_GET['visitor_id'])) {
                    $db = new PHPWS_DB('checkin_visitor');
                    $db->addValue('assigned', (int)$_GET['staff_id']);
                    $db->addWhere('id', (int)$_GET['visitor_id']);
                    PHPWS_Error::logIfError($db->update());
                    printf('staff_id %s, visitor_id %s', $_GET['staff_id'], $_GET['visitor_id']);
                }
            }
            exit();
            break;

        case 'assign':
            if (Current_User::allow('checkin', 'assign_visitors')) {
                $this->panel->setCurrentTab('assign');
                $this->assign();
            }
            break;

        case 'post_note':
            $this->loadVisitor();
            $this->saveNote();
            PHPWS_Core::goBack();
            break;

        case 'hide_panel':
            PHPWS_Cookie::write('checkin_hide_panel', 1);
            PHPWS_Core::goBack();
            break;

        case 'show_panel':
            PHPWS_Cookie::delete('checkin_hide_panel');
            PHPWS_Core::goBack();
            $this->panel->setCurrentTab('assign');
            $this->assign();
            break;

        case 'hide_sidebar':
            PHPWS_Cookie::write('checkin_hide_sidebar', 1);
            PHPWS_Core::goBack();
            $this->panel->setCurrentTab('assign');
            $this->use_sidebar = false;
            $this->assign();
            break;

        case 'show_sidebar':
            PHPWS_Cookie::delete('checkin_hide_sidebar');
            PHPWS_Core::goBack();
            $this->panel->setCurrentTab('assign');
            $this->assign();
            break;

        case 'waiting':
            $this->panel->setCurrentTab('waiting');
            $this->loadCurrentStaff();
            $this->waiting();
            break;

        case 'small_wait':
            $this->loadCurrentStaff();
            $this->waiting(true);
            $js = true;
            break;

        case 'remove_visitor':
            if (Current_User::allow('checkin', 'remove_visitors')) {
                $this->removeVisitor();
            }
            PHPWS_Core::goBack();
            break;

        case 'settings':
            if (Current_User::allow('checkin', 'settings')) {
                $this->panel->setCurrentTab('settings');
                $this->settings();
            }
            break;

        case 'reasons':
            if (Current_User::allow('checkin', 'settings')) {
                $this->panel->setCurrentTab('reasons');
                $this->reasons();
            }
            break;

        case 'post_reason':
            if (Current_User::allow('checkin', 'settings')) {
                $this->loadReason();
                if ($this->postReason()) {
                    $this->reason->save();
                    PHPWS_Core::reroute('index.php?module=checkin&tab=reasons');
                } else {
                    $this->editReason();
                }
            }
            break;

        case 'staff':
            $this->panel->setCurrentTab('staff');
            $this->staff();
            break;


        case 'edit_staff':
            if (Current_User::allow('checkin', 'settings')) {
                $this->loadStaff(null, true);
                $this->editStaff();
            }
            break;

        case 'search_users':
            $this->searchUsers();
            break;

        case 'update_reason':
            if (Current_User::allow('checkin', 'settings')) {
                if (Current_User::authorized('checkin', 'settings')) {
                    $this->updateReason();
                }
                $this->panel->setCurrentTab('settings');
                $this->settings();
            }
            break;

        case 'post_staff':
            if (!Current_User::authorized('checkin', 'settings')) {
                Current_User::disallow();
            }
            if ($this->postStaff()) {
                // save post
                $this->staff->save();
                PHPWS_Core::reroute('index.php?module=checkin&tab=staff');
            } else {
                // post failed
                $this->loadStaff();
                $this->editStaff();
            }

            break;

        case 'post_settings':
            // from Checkin_Admin::settings
            if (Current_User::authorized('checkin', 'settings')) {
                $this->postSettings();
            }

            PHPWS_Core::reroute('index.php?module=checkin&tab=settings');
            break;

        case 'edit_reason':
            $this->loadReason();
            $this->editReason();
            break;

        case 'delete_reason':
            $this->loadReason();
            $this->reason->delete();
            PHPWS_Core::goBack();
            break;

        }

        if (empty($this->content)) {
            $this->content = dgettext('checkin', 'Command not recognized.');
        }

        if ($js) {
            $tpl['TITLE'] = & $this->title;
            $tpl['CONTENT'] = & $this->content;
            $tpl['MESSAGE'] = & $this->message;
            $content = PHPWS_Template::process($tpl, 'checkin', 'main.tpl');
            Layout::nakedDisplay($content, $this->title);
        } else {
            if (is_array($this->message)) {
                $this->message = implode('<br />', $this->message);
            }

            if (!$this->use_sidebar) {
                Layout::collapse();
            }

            if ($this->use_panel) {
                Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
            } else {
                $tpl['TITLE'] = & $this->title;
                $tpl['CONTENT'] = & $this->content;
                $tpl['MESSAGE'] = & $this->message;

                Layout::add(PHPWS_Template::process($tpl, 'checkin', 'main.tpl'));
            }
        }
    }

    function loadPanel()
    {
        $link = 'index.php?module=checkin';

        $tabs['waiting'] = array('title'=>dgettext('checkin', 'Waiting'),
                                 'link'=>$link);

        if (Current_User::allow('checkin', 'assign_visitors')) {
            $tabs['assign'] = array('title'=>dgettext('checkin', 'Assignment'),
                                    'link'=>$link);
        }


        if (Current_User::allow('checkin', 'settings')) {
            $tabs['staff'] =  array('title'=>dgettext('checkin', 'Staff'),
                                    'link'=>$link);

            $tabs['reasons']  = array('title'=>dgettext('checkin', 'Reasons'),
                                      'link'=>$link);

            $tabs['settings'] = array('title'=>dgettext('checkin', 'Settings'),
                                      'link'=>$link);
        }

        $tabs['report'] = array('title'=>dgettext('checkin', 'Report'),
                                'link'=>$link);

        $this->panel = new PHPWS_Panel('check-admin');
        $this->panel->quickSetTabs($tabs);
    }

    function assign()
    {
        Layout::addStyle('checkin');
        javascript('modules/checkin/send_note');
        javascript('modules/checkin/reassign', array('authkey'=>Current_User::getAuthKey()));
        $this->title = dgettext('checkin', 'Assignment');
        $this->loadVisitorList(null, true);
        $this->loadStaffList();

        // id and name only for drop down menu
        $staff_list = $this->getStaffList();
        $staff_list = array_reverse($staff_list, true);
        $staff_list[0] = dgettext('checkin', 'Unassigned');
        $staff_list[-1] = dgettext('checkin', '-- Move visitor --');
        $staff_list = array_reverse($staff_list, true);

        if (empty($this->staff_list)) {
            $this->content = dgettext('checkin', 'No staff found.');
            return;
        }

        $status_list = $this->getStatusColors();
        // unassigned visitors

        $staff = new Checkin_Staff;
        $staff->display_name = dgettext('checkin', 'Unassigned');

        $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
        $row['COLOR']    = '#ffffff';
        $row['DISPLAY_NAME'] = $staff->display_name;
        $tpl['rows'][] = $row;

        // Go through staff and list assignments
        foreach ($this->staff_list as $staff) {
            $row = array();
            $this->current_staff = & $staff;
            $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
            $row['COLOR']    = $status_list[$staff->status];
            $row['DISPLAY_NAME'] = $staff->display_name;

            if (!isset($this->visitor_list[$staff->id])) {
                $this->current_visitor = null;
            } else {
                $this->current_visitor = & $this->visitor_list[$staff->id][0];
            }

            $this->statusButtons($row);
            $tpl['rows'][] = $row;
        }
        $tpl['VISITORS_LABEL'] = dgettext('checkin', 'Visitors');
        $tpl['DISPLAY_NAME_LABEL'] = dgettext('checkin', 'Staff name');
        $tpl['TIME_WAITING_LABEL'] = dgettext('checkin', 'Time waiting');

        $tpl['HIDE_PANEL'] = $this->hidePanelLink();
        $tpl['HIDE_SIDEBAR'] = $this->hideSidebarLink();

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'visitors.tpl');
        Layout::metaRoute('index.php?module=checkin&aop=assign', PHPWS_Settings::get('checkin', 'assign_refresh'));
    }

    function hideSidebarLink()
    {
        if (PHPWS_Cookie::read('checkin_hide_sidebar') || $this->use_sidebar == false) {
            $this->use_sidebar = false;
            return PHPWS_Text::moduleLink(dgettext('checkin', 'Show sidebar'), 'checkin', array('aop'=>'show_sidebar'));
        } else {
            return PHPWS_Text::moduleLink(dgettext('checkin', 'Hide sidebar'), 'checkin', array('aop'=>'hide_sidebar'));
        }
    }

    function hidePanelLink()
    {
        if (PHPWS_Cookie::read('checkin_hide_panel') || $this->use_panel == false) {
            $this->use_panel = false;
            return PHPWS_Text::moduleLink(dgettext('checkin', 'Show panel'), 'checkin', array('aop'=>'show_panel'));
        } else {
            return PHPWS_Text::moduleLink(dgettext('checkin', 'Hide panel'), 'checkin', array('aop'=>'hide_panel'));
        }
    }

    function listVisitors($staff, $staff_list)
    {
        if (empty($this->visitor_list[$staff->id])) {
            return dgettext('checkin', 'No visitors waiting');
        }
        $vis_list = $this->visitor_list[$staff->id];
        unset($staff_list[$staff->id]);

        foreach ($vis_list as $vis) {
            $row['list'][] = $vis->row($staff_list, $staff);
        }

        $row['NAME_LABEL'] = dgettext('checkin', 'Name / Reason / Note');
        $row['WAITING_LABEL'] = dgettext('checkin', 'Time waiting');
        $row['ACTION_LABEL'] = dgettext('checkin', 'Action');
        return PHPWS_Template::process($row, 'checkin', 'queue.tpl');
    }

    function waiting($small_view=false)
    {
        Layout::addStyle('checkin');
        javascript('modules/checkin/send_note/');
        if (!$this->current_staff) {
            $this->content = dgettext('checkin', 'You are not a staff member.');
        }
        $this->loadVisitorList($this->current_staff->id);


        $this->title = dgettext('checkin', 'Waiting list');
        if (empty($this->visitor_list)) {
            $tpl['MESSAGE'] = dgettext('checkin', 'You currently do not have any visitors.');
            $this->loadVisitorList(0);
            if (!empty($this->visitor_list)) {
                $tpl['MESSAGE'] .= '<br />' . sprintf(dgettext('checkin', 'There are %s unassigned visitors.'), count($this->visitor_list));
            }
        } else {
            foreach ($this->visitor_list as $vis) {
                if ($vis->id == $this->current_staff->visitor_id) {
                    $current_vis = $vis;
                    continue;
                }
                if (!isset($first_visitor)) {
                    $first_visitor = $vis->id;
                }
                $row = $links = array();
                $row = $vis->row(null, $this->current_staff);
                $tpl['list'][] = $row;
            }
        }

        $this->current_visitor = $this->visitor_list[0];
        $this->statusButtons($tpl);

        if ($small_view) {
            $tpl['REDUCE'] = ' ';
            $tpl['CLOSE'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', dgettext('checkin', 'Close'));
            Layout::metaRoute('index.php?module=checkin&aop=small_wait', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        } else {
            $tpl['HIDE_PANEL'] = $this->hidePanelLink();
            $tpl['HIDE_SIDEBAR'] = $this->hideSidebarLink();
            $tpl['SMALL_VIEW'] = $this->smallViewLink();
            Layout::metaRoute('index.php?module=checkin&aop=waiting', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        }


        $tpl['NAME_LABEL'] = dgettext('checkin', 'Name / Notes');
        $tpl['WAITING_LABEL'] = dgettext('checkin', 'Time waiting');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'waiting.tpl');
    }

    function smallViewLink()
    {
        $vars['aop'] = 'small_wait';
        $js['address'] = PHPWS_Text::linkAddress('checkin', $vars, true);
        $js['label']   = dgettext('checkin', 'Small view');
        $js['width'] = '640';
        $js['height'] = '480';
        return javascript('open_window', $js);
    }

    function statusButtons(&$tpl)
    {
        switch ($this->current_staff->status) {
        case 0:
            // Available
            if (!empty($this->visitor_list) && $this->current_visitor) {
                $tpl['MEET'] = $this->startMeetingLink();
            }
            $tpl['UNAVAILABLE'] = $this->unavailableLink();
            $tpl['CURRENT_MEETING'] = dgettext('checkin', 'You are currently available for meeting.');
            $tpl['CURRENT_CLASS'] = 'available';
            break;

        case 1:
            $tpl['AVAILABLE'] = $this->availableLink();
            $tpl['CURRENT_MEETING'] = dgettext('checkin', 'You are currently unavailable.');
            $tpl['CURRENT_CLASS'] = 'unavailable';
            break;

        case 2:
            $tpl['FINISH'] = $this->finishLink();
            $this->loadVisitor($this->current_staff->visitor_id);

            $tpl['CURRENT_MEETING'] = sprintf(dgettext('checkin', 'You are currently meeting with %s.'), $this->visitor->getName());
            $tpl['CURRENT_CLASS'] = 'meeting';
            break;
        }
    }

    function finishLink()
    {
        $vars['aop'] = 'finish_meeting';
        $vars['visitor_id'] = $this->current_visitor->id;
        $vars['staff_id'] = $this->current_staff->id;
        $title = sprintf(dgettext('checkin', 'Finish meeting with %s %s'), $this->current_visitor->firstname, $this->current_visitor->lastname);
        return PHPWS_Text::secureLink($title, 'checkin', $vars, null, $title, 'finish-button action-button');
    }

    function unavailableLink()
    {
        $vars['aop'] = 'unavailable';
        $vars['staff_id'] = $this->current_staff->id;
        return PHPWS_Text::secureLink(dgettext('checkin', 'Unavailable'), 'checkin', $vars, null, dgettext('checkin', 'Unavailable for meeting'), 'unavailable-button action-button');
    }

    function availableLink()
    {
        $vars['aop'] = 'available';
        $vars['staff_id'] = $this->current_staff->id;
        return PHPWS_Text::secureLink(dgettext('checkin', 'Available'), 'checkin', $vars, null, dgettext('checkin', 'Available for meeting'), 'available-button action-button');
    }

    function startMeetingLink()
    {
        $vars['aop'] = 'start_meeting';
        $vars['staff_id'] = $this->current_staff->id;
        $vars['visitor_id'] = $this->current_visitor->id;
        $title = sprintf(dgettext('checkin', 'Start meeting w/ %s'), $this->current_visitor->getName());
        return PHPWS_Text::secureLink($title, 'checkin', $vars, null, $title, 'meet-button action-button');
    }

    function staff()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('checkin', 'Staff.php');

        $page_tags['ADD_STAFF']    = $this->addStaffLink();
        $page_tags['FILTER_LABEL'] = dgettext('checkin', 'Filter');

        $pager = new DBPager('checkin_staff', 'Checkin_Staff');
        $pager->setTemplate('staff.tpl');
        $pager->setModule('checkin');
        $pager->addRowTags('row_tags');
        $pager->setEmptyMessage(dgettext('checkin', 'No staff found.'));
        $pager->addPageTags($page_tags);
        $pager->joinResult('user_id', 'users', 'id', 'display_name');
        $pager->addSortHeader('filter', 'Filter');
        $pager->addSortHeader('display_name', 'Staff name');

        $this->title = dgettext('checkin', 'Staff');
        $this->content = $pager->get();
    }

    function editStaff()
    {
        $form = new PHPWS_Form('edit-staff');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_staff');
        if (!$this->staff->id) {
            javascript('jquery');
            javascript('modules/checkin/search_user');

            $this->title = dgettext('checkin', 'Add staff member');
            $form->addText('username');
            $form->setLabel('username', dgettext('checkin', 'Staff user name'));
            $form->addSubmit(dgettext('checkin', 'Add staff'));
        } else {
            $this->title = dgettext('checkin', 'Edit staff member');
            $form->addHidden('staff_id', $this->staff->id);
            $form->addTplTag('USERNAME', $this->staff->display_name);
            $form->addTplTag('USERNAME_LABEL', dgettext('checkin', 'Staff user name'));
            $form->addSubmit(dgettext('checkin', 'Update staff'));
        }

        $reasons = $this->getReasons();

        if (empty($reasons)) {
            $form->addTplTag('REASONS', PHPWS_Text::moduleLink(dgettext('checkin', 'No reasons found.'), 'checkin',
                                                               array('aop'=>'reasons')));
            $form->addTplTag('REASONS_LABEL',  dgettext('checkin', 'Reasons'));
            $form->addRadioAssoc('filter_type', array(0               =>dgettext('checkin', 'None'),
                                                      CO_FT_LAST_NAME =>dgettext('checkin', 'Last name')));
        } else {
            $form->addMultiple('reasons', $reasons);
            $form->setMatch('reasons', $this->staff->_reasons);
            $form->setLabel('reasons', dgettext('checkin', 'Reasons'));
            $form->addRadioAssoc('filter_type', array(0               =>dgettext('checkin', 'None'),
                                                      CO_FT_LAST_NAME =>dgettext('checkin', 'Last name'),
                                                      CO_FT_REASON    =>dgettext('checkin', 'Reason')));

        }

        $form->setMatch('filter_type', $this->staff->filter_type);

        $form->addText('last_name_filter', $this->staff->filter);
        $form->setLabel('last_name_filter', dgettext('checkin', 'Example: a,b,ca-cf,d'));

        $tpl = $form->getTemplate();

        $tpl['FILTER_LEGEND'] = dgettext('checkin', 'Visitor filter');

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'edit_staff.tpl');
    }

    function reasons()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('checkin', 'Reasons.php');

        $pt['MESSAGE_LABEL'] = dgettext('checkin', 'Submission message');
        $pt['ADD_REASON'] = PHPWS_Text::secureLink(dgettext('checkin', 'Add reason'), 'checkin',
                                                   array('aop'=>'edit_reason'));

        $pager = new DBPager('checkin_reasons', 'Checkin_Reasons');
        $pager->setModule('checkin');
        $pager->setTemplate('reasons.tpl');
        $pager->addPageTags($pt);
        $pager->addSortHeader('id', dgettext('checkin', 'Id'));
        $pager->addSortHeader('summary', dgettext('checkin', 'Summary'));
        $pager->addRowTags('rowTags');

        $this->title = dgettext('checkin', 'Reasons');
        $this->content = $pager->get();

    }

    function editReason()
    {
        $reason =  & $this->reason;

        $form = new PHPWS_Form('edit-reason');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_reason');
        $form->addHidden('reason_id', $reason->id);

        $form->addText('summary', $reason->summary);
        $form->setSize('summary', 40);
        $form->setLabel('summary', dgettext('checkin', 'Summary'));
        $form->setRequired('summary');

        $form->addTextArea('message', $reason->message);
        $form->setRequired('message');
        $form->setLabel('message', dgettext('checkin', 'Completion message'));

        if ($reason->id) {
            $this->title = dgettext('checkin', 'Update reason');
            $form->addSubmit('go', dgettext('checkin', 'Update'));
        } else {
            $this->title = dgettext('checkin', 'Add new reason');
            $form->addSubmit('go', dgettext('checkin', 'Add'));
        }
        $template = $form->getTemplate();
        $this->content = PHPWS_Template::process($template, 'checkin', 'edit_reason.tpl');
    }

    function settings()
    {
        $this->title = dgettext('checkin', 'Settings');
        javascript('jquery');
        $form = new PHPWS_Form('settings');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_settings');
        $form->addCheck('front_page', 1);
        $form->setMatch('front_page', PHPWS_Settings::get('checkin', 'front_page'));
        $form->setLabel('front_page', dgettext('checkin', 'Show public sign-in on front page'));

        $form->addText('assign_refresh', PHPWS_Settings::get('checkin', 'assign_refresh'));
        $form->setSize('assign_refresh', '3');
        $form->setLabel('assign_refresh', dgettext('checkin', 'Assignment page refresh rate (in seconds)'));

        $form->addText('waiting_refresh', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        $form->setSize('waiting_refresh', '3');
        $form->setLabel('waiting_refresh', dgettext('checkin', 'Waiting page refresh rate (in seconds)'));

        $form->addCheck('collapse_signin', 1);
        $form->setLabel('collapse_signin', dgettext('checkin', 'Hide sidebar for visitors'));
        $form->setMatch('collapse_signin', PHPWS_Settings::get('checkin', 'collapse_signin'));

        $form->addSubmit(dgettext('checkin', 'Save settings'));
        $tpl = $form->getTemplate();

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'setting.tpl');
    }

    function postSettings()
    {
        if (isset($_POST['add'])) {
            $reason = trim(strip_tags($_POST['new_reason']));
            if (!empty($reason)) {
                $this->addReason($reason);
            }
        }

        PHPWS_Settings::set('checkin', 'front_page', (int)isset($_POST['front_page']));
        PHPWS_Settings::set('checkin', 'collapse_signin', (int)isset($_POST['collapse_signin']));

        $seconds = (int)$_POST['assign_refresh'];
        if ($seconds < 1) {
            $seconds = 15;
        }

        PHPWS_Settings::set('checkin', 'assign_refresh', $seconds);
        PHPWS_Settings::save('checkin');
    }

    function addStaffLink()
    {
        return PHPWS_Text::secureLink(dgettext('checkin', 'Add staff member'), 'checkin', array('aop'=>'edit_staff'));
    }


    function searchUsers()
    {
        if (!Current_User::isLogged()) {
            exit();
        }
        $db = new PHPWS_DB('users');
        if (empty($_GET['q'])) {
            exit();
        }

        $username = preg_replace('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', '', $_GET['q']);
        $db->addWhere('username', "$username%", 'like');
        $db->addColumn('username');
        $result = $db->select('col');
        if (!empty($result) && !PHPWS_Error::logIfError($result)) {
            echo implode("\n", $result);
        }
        exit();
    }

    function addReason($reason)
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addValue('summary', $reason);
        return !PHPWS_Error::logIfError($db->insert());
    }

    /**
     * Removes a reason from the reason table
     */
    function deleteReason($reason_id)
    {
        if (empty($reason_id) || !is_numeric($reason_id)) {
            return false;
        }

        $db = new PHPWS_DB('checkin_reasons');
        $db->addWhere('id', (int)$reason_id);
        return $db->delete();
    }

    function updateReason()
    {
        if (empty($_GET['reason_id']) || empty($_GET['reason'])) {
            return;
        }

        $db = new PHPWS_DB('checkin_reasons');
        $db->addWhere('id', (int)$_GET['reason_id']);
        $db->addValue('summary', strip_tags($_GET['reason']));
        return !PHPWS_Error::logIfError($db->update());
    }

    function postStaff()
    {
        @$staff_id  = (int)$_POST['staff_id'];

        if (!empty($staff_id)) {
            $this->loadStaff($staff_id);
        } else {
            @$user_name = $_POST['username'];
            if (empty($user_name) || !Current_User::allowUsername($user_name)) {
                $this->message = dgettext('checkin', 'Please try another user name');
                return false;
            }

            // Test user name, make sure exists
            $db = new PHPWS_DB('checkin_staff');
            $db->addWhere('user_id', 'users.id');
            $db->addWhere('users.username', $user_name);
            $db->addColumn('id');
            $result = $db->select('one');
            if (PHPWS_Error::logIfError($result)) {
                $this->message = dgettext('checkin', 'Problem saving user.');
                return false;
            } elseif ($result) {
                $this->message = dgettext('checkin', 'User already is staff member.');
                return false;
            }

            // user is allowed and new, get user_id to create staff
            $db = new PHPWS_DB('users');
            $db->addWhere('username', $user_name);
            $db->addColumn('id');
            $user_id = $db->select('one');
            if (PHPWS_Error::logIfError($result)) {
                $this->message = dgettext('checkin', 'Problem saving user.');
                return false;
            }

            if (!$user_id) {
                $this->message = dgettext('checkin', 'Could not locate anyone with this user name.');
                return false;
            }
            $this->loadStaff();
            $this->staff->user_id = $user_id;
        }

        $this->staff->filter_type = (int)$_POST['filter_type'];
        $this->staff->parseFilter($_POST['last_name_filter']);

        if (!empty($_POST['reasons'])) {
            $this->staff->_reasons = $_POST['reasons'];
        }

        return true;
    }

    function postReason()
    {
        $this->reason->summary = $_POST['summary'];
        $this->reason->message = $_POST['message'];
        if (empty($this->reason->summary)) {
            $this->message[] = dgettext('checkin', 'Please enter the summary.');
        }

        if (empty($this->reason->message)) {
            $this->message[] = dgettext('checkin', 'Please enter a completion message.');
        }

        return empty($this->message);
    }

    function assignmentLink()
    {
        $vars['aop'] = 'assign';
        return PHPWS_Text::secureLink(dgettext('checkin', 'Assignment page'), 'checkin', $vars);
    }

    function waitingLink()
    {
        $vars['aop'] = 'waiting';
        return PHPWS_Text::secureLink(dgettext('checkin', 'Waiting page'), 'checkin', $vars);
    }

    function menu()
    {
        $tpl['WAITING'] = $this->waitingLink();
        $tpl['ASSIGN_PAGE'] = $this->assignmentLink();
        $tpl['TITLE'] = dgettext('checkin', 'Checkin Menu');
        $content = PHPWS_Template::process($tpl, 'checkin', 'menu.tpl');
        Layout::add($content, 'checkin', 'checkin-admin');
    }

    function loadCurrentStaff()
    {
        PHPWS_Core::initModClass('checkin', 'Staff.php');
        if (empty($this->current_staff)) {
            $staff = new Checkin_Staff(Current_User::getId());
            if ($staff->id) {
                $this->current_staff = & $staff;
            }
        }
    }

    function saveNote()
    {
        $this->visitor->note = strip_tags(trim($_POST['note_body']));
        PHPWS_Error::logIfError($this->visitor->save());
    }

    function startMeeting()
    {
        $this->loadStaff();
        $this->loadVisitor();

        // set staff to meeting status and with current visitor
        $this->staff->status = 2;
        $this->staff->visitor_id = $this->visitor->id;
        $this->staff->save();

        $this->visitor->start_meeting = mktime();
        $this->visitor->save();
    }

    function finishMeeting()
    {
        $this->loadStaff();
        $this->loadVisitor();

        // set staff to meeting status and with current visitor
        $this->staff->status = 0;
        $this->staff->visitor_id = 0;
        $this->staff->save();

        $this->visitor->end_meeting = mktime();
        $this->visitor->finished = true;
        $this->visitor->save();
    }

    function unavailable()
    {
        $this->loadStaff();
        $this->staff->status = 1;
        $this->staff->save();
    }

    function available()
    {
        $this->loadStaff();
        $this->staff->status = 0;
        $this->staff->save();
    }


    function report($print=false)
    {
        $tpl = array();

        if (isset($_GET['udate'])) {
            $udate = (int)$_GET['udate'];
        } elseif (isset($_GET['cdate'])) {
            $udate = strtotime($_GET['cdate']);
        } else {
            $udate = mktime(0,0,0);
        }
        $current_date = strftime('%Y/%m/%d', $udate);

        $this->title = sprintf(dgettext('checkin', 'Report for %s'), strftime('%e %B, %Y', $udate));

        if (!$print) {
            $form = new PHPWS_Form('report-date');
            $form->setMethod('get');
            $form->addHidden('module', 'checkin');
            $form->addText('cdate', $current_date);
            $form->addHidden('aop', 'report');
            $form->setLabel('cdate', dgettext('checkin', 'Date'));
            $form->addSubmit(dgettext('checkin', 'Go'));
            $tpl = $form->getTemplate();

            $js['form_name'] = 'report-date';
            $js['date_name'] = 'cdate';
            $tpl['CAL'] = javascript('js_calendar', $js);
            $tpl['PRINT_LINK'] = PHPWS_Text::secureLink(dgettext('checkin', 'Print view'), 'checkin',
                                                        array('aop'=>'report',
                                                              'print'=>1,
                                                              'udate'=>$udate));
        }

        $tObj = new PHPWS_Template('checkin');
        $tObj->setFile('report.tpl');

        $this->loadStaffList();
        $reasons = $this->getReasons();

        PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $db = new PHPWS_DB('checkin_visitor');
        $db->addWhere('start_meeting', $udate, '>=');
        $db->addWhere('end_meeting', $udate + 86400, '<');
        $db->addWhere('finished', 1);
        $db->setIndexBy('assigned', true);
        $visitors = $db->getObjects('Checkin_Visitor');

        $row['NAME_LABEL'] = dgettext('checkin', 'Name, Reason, & Note');
        $row['WAITED_LABEL'] = dgettext('checkin', 'Time waited');

        foreach ($this->staff_list as $staff) {
            $total_wait = $count = 0;
            if (isset($visitors[$staff->id])) {
                foreach ($visitors[$staff->id] as $vis) {
                    $wait = $vis->start_meeting - $vis->arrival_time;
                    $tObj->setCurrentBlock('subrow');
                    $tObj->setData(array('VIS_NAME' => $vis->getName(),
                                         'REASON'   => $reasons[$vis->reason],
                                         'NOTE'     => $vis->note,
                                         'WAITED'   => Checkin::timeWaiting($wait)));
                    $tObj->parseCurrentBlock();
                    $count++;
                    $total_wait += $wait;
                }
            } else {
                $tObj->setCurrentBlock('message');
                $tObj->setData(array('NOBODY' => dgettext('checkin', 'No visitors seen')));
                $tObj->parseCurrentBlock();
            }
            $tObj->setCurrentBlock('row');
            $row['DISPLAY_NAME'] = & $staff->display_name;
            $row['VISITORS_SEEN'] = sprintf(dgettext('checkin', 'Visitors seen: %s'), $count);
            $row['TOTAL_WAIT'] = sprintf(dgettext('checkin', 'Total wait time: %s'), Checkin::timeWaiting($total_wait));
            $tObj->setData($row);
            $tObj->parseCurrentBlock();
        }

        $tObj->setData($tpl);
        $this->content = $tObj->get();
    }

    function removeVisitor()
    {
        $this->loadVisitor();
        $this->visitor->delete();
    }
}

?>