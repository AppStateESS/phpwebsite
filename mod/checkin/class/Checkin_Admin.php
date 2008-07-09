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
    var $panel         = null;
    var $use_panel     = true;
    var $current_staff = null;

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
        case 'reassign':
            // Called via ajax
            if (Current_User::authorized('checkin')) {
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
            $this->panel->setCurrentTab('assign');
            $this->assign();
            break;

        case 'post_note':
            $this->loadVisitor();
            $this->saveNote();
            PHPWS_Core::goBack();
            break;

        case 'hide_panel':
            PHPWS_Cookie::write('checkin_hide_panel', 1); 
            $this->panel->setCurrentTab('assign');
            $this->use_panel = false;
            $this->assign();
            break;

        case 'show_panel':
            PHPWS_Cookie::delete('checkin_hide_panel');
            $this->panel->setCurrentTab('assign');
            $this->assign();
            break;

        case 'waiting':
            $this->panel->setCurrentTab('waiting');
            $this->loadCurrentStaff();
            $this->waiting();
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
            $this->loadReason();
            if ($this->postReason()) {
                $this->reason->save();
                PHPWS_Core::reroute('index.php?module=checkin&tab=reasons');
            } else {
                $this->editReason();
            }
            break;
           
        case 'staff':
            $this->panel->setCurrentTab('staff');
            $this->staff();
            break;


        case 'edit_status':
            $this->loadStatus();
            $this->editStatus();
            break;
            

        case 'edit_staff':
            $this->loadStaff(null, true);
            $this->editStaff();
            break;

        case 'search_users':
            $this->searchUsers();
            break;

        case 'status':
            $this->statusList();
            break;

        case 'update_reason':
            if (Current_User::authorized('checkin', 'settings')) {
                $this->updateReason();
            }
            $this->panel->setCurrentTab('settings');
            $this->settings();
            break;

        case 'post_staff':
            if (!Current_User::authorized('checkin', 'edit_staff')) {
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

        case 'post_status':
            if (!Current_User::authorized('checkin', 'settings')) {
                Current_User::disallow();
            } 
            $this->loadStatus();
            if (!$this->postStatus()) {
                $this->editStatus();
            } else {
                PHPWS_Error::logIfError($this->status->save());
                PHPWS_Core::reroute('index.php?module=checkin&tab=status');
            }
            break;

        case 'post_settings':
            // from Checkin_Admin::settings
            if (Current_User::authorized('checkin', 'settings')) {
                $this->postSettings();
            }

            if (isset($_POST['delete'])) {
                $this->deleteReason($_POST['edit_reason']);
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
            Layout::nakedDisplay($this->content, $this->title);
        } else {
            if (is_array($this->message)) {
                $this->message = implode('<br />', $this->message);
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

        if (Current_User::allow('checkin', 'assign_filters')) {
            $tabs['assign'] = array('title'=>dgettext('checkin', 'Assignment'),
                                    'link'=>$link);
        }


        if (Current_User::allow('checking', 'settings')) {
            $tabs['staff'] =  array('title'=>dgettext('checkin', 'Staff'),
                                    'link'=>$link);

            $tabs['reasons']  = array('title'=>dgettext('checkin', 'Reasons'),
                                      'link'=>$link);

            $tabs['status']   = array('title'=>dgettext('checkin', 'Status'),
                                      'link'=>$link);

            $tabs['settings'] = array('title'=>dgettext('checkin', 'Settings'),
                                      'link'=>$link);
        }

        $this->panel = new PHPWS_Panel('check-admin');
        $this->panel->quickSetTabs($tabs);
    }

    function assign()
    {
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

        $status_list = $this->getStatusList();
        // unassigned visitors

        $staff = new Checkin_Staff;
        $staff->display_name = dgettext('checkin', 'Unassigned');
        
        $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
        $row['COLOR']    = '#ffffff';
        $row['DISPLAY_NAME'] = $staff->display_name;
        $tpl['rows'][] = $row;

        // Go through staff and list assignments
        foreach ($this->staff_list as $staff) {
            $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
            $row['COLOR']    = $status_list[$staff->status]['color'];
            $row['DISPLAY_NAME'] = $staff->display_name;
            $tpl['rows'][] = $row;
        }
        $tpl['VISITORS_LABEL'] = dgettext('checkin', 'Visitors');
        $tpl['DISPLAY_NAME_LABEL'] = dgettext('checkin', 'Staff name');
        $tpl['TIME_WAITING_LABEL'] = dgettext('checkin', 'Time waiting');

        if (PHPWS_Cookie::read('checkin_hide_panel') || $this->use_panel == false) {
            $this->use_panel = false;
            $tpl['HIDE_PANEL'] = PHPWS_Text::moduleLink(dgettext('checkin', 'Show panel'), 'checkin', array('aop'=>'show_panel'));
        } else {
            $tpl['HIDE_PANEL'] = PHPWS_Text::moduleLink(dgettext('checkin', 'Hide panel'), 'checkin', array('aop'=>'hide_panel'));
        }


        $this->content = PHPWS_Template::process($tpl, 'checkin', 'visitors.tpl');
    }

    function listVisitors($staff, $staff_list)
    {
        $vis_list = & $this->visitor_list[$staff->id];
        if (empty($vis_list)) {
            return dgettext('checkin', 'No visitors waiting');
        }

        unset($staff_list[$staff->id]);



        if (is_array($vis_list)) {
            foreach ($vis_list as $vis) {
                $row['list'][] = $vis->row($staff_list);
            }
        } else {
            $row['list'][] = $vis_list->row();
        }
        $row['NAME_LABEL'] = dgettext('checkin', 'Name');
        $row['WAITING_LABEL'] = dgettext('checkin', 'Time waiting');
        return PHPWS_Template::process($row, 'checkin', 'queue.tpl');
    }

    function waiting()
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
                $row = $links = array();
                $row = $vis->row();
                $tpl['list'][] = $row;
            }
        }
        $tpl['NAME_LABEL'] = dgettext('checkin', 'Name / Notes');
        $tpl['WAITING_LABEL'] = dgettext('checkin', 'Time waiting');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'waiting.tpl');
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
        
        $form->addRadioAssoc('filter_type', array(0               =>dgettext('checkin', 'None'),
                                                  CO_FT_LAST_NAME =>dgettext('checkin', 'Last name'),
                                                  CO_FT_REASON    =>dgettext('checkin', 'Reason')));
        $form->setMatch('filter_type', $this->staff->filter_type);

        $form->addText('last_name_filter', $this->staff->filter);
        $form->setLabel('last_name_filter', dgettext('checkin', 'Example: a,b,ca-cf,d'));

        $reasons = $this->getReasons();

        if (empty($reasons)) {
            $form->addTplTag('REASONS', PHPWS_Text::moduleLink(dgettext('checkin', 'No reasons found.'), 'checkin',
                                                               array('aop'=>'settings')));
            $form->addTplTag('REASONS_LABEL',  dgettext('checkin', 'Reasons'));
        } else {
            $form->addMultiple('reasons', $reasons);
            $form->setMatch('reasons', $this->staff->_reasons);
            $form->setLabel('reasons', dgettext('checkin', 'Reasons'));
        }

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
        $form->addCheck('front_page', 1);
        $form->setMatch('front_page', PHPWS_Settings::get('checkin', 'front_page'));
        $form->setLabel('front_page', dgettext('checkin', 'Show public sign-in on front page'));

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

        $front_page = (int)isset($_POST['front_page']);
        PHPWS_Settings::set('checkin', 'front_page', $front_page);
        PHPWS_Settings::save('checkin');
    }

    function addStatusLink()
    {
        return PHPWS_Text::secureLink(dgettext('checkin', 'Add status'), 'checkin', array('aop'=>'edit_status'));
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

    function menu()
    {
        $form = new PHPWS_Form('checkin-menu');
        $form->setMethod('get');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'view_staff');

        $staff_list = $this->getStaffList();
        $form->addSelect('staff_list', $staff_list);
        $form->addSubmit('go', dgettext('checkin', 'View staff'));

        $tpl = $form->getTemplate();
        $tpl['ASSIGN_PAGE'] = $this->assignmentLink();
        $tpl['TITLE'] = dgettext('checkin', 'Checkin Menu');
        $content = PHPWS_Template::process($tpl, 'checkin', 'menu.tpl');
        Layout::add($content, 'checkin', 'checkin-admin');
    }

    function statusList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('checkin', 'Status.php');
        $page_tags['ADD_STATUS']      = $this->addStatusLink();
        $page_tags['COLOR_LABEL']     = dgettext('checkin', 'Color');
        $page_tags['ACTION_LABEL'] = dgettext('checkin', 'Action');

        $pager = new DBPager('checkin_status', 'Checkin_Status');
        $pager->setTemplate('status.tpl');
        $pager->setModule('checkin');
        $pager->addRowTags('row_tags');
        $pager->addPageTags($page_tags);
        $pager->addSortHeader('summary', dgettext('checkin', 'Summary'));
        $this->title   = dgettext('checkin', 'Status');
        $this->content = $pager->get();
    }

    function editStatus()
    {
        $form = new PHPWS_Form('checkin-status');
        if ($this->status->id) {
            $this->title = dgettext('checkin', 'Update status');
        } else {
            $this->title = dgettext('checkin', 'Add status');
        }

        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_status');
        $form->addHidden('status_id', $this->status->id);

        $form->addText('summary', $this->status->summary);
        $form->setLabel('summary', dgettext('checkin', 'Summary'));
        $form->setSize('summary', 40);
        $form->setRequired('summary');

        $form->addText('color', $this->status->color);
        $form->setLabel('color', dgettext('checkin', 'Color'));
        $form->setSize('color', 7, 7);
        $form->setRequired('color');

        $form->addSelect('available', array(1 => dgettext('checkin', 'Occupied with visitor'),
                                            2 => dgettext('checkin', 'Unavailable')));
        $form->setMatch('available', $this->status->available);
        $form->setLabel('available', dgettext('checkin', 'Availability'));

        $form->addSubmit(dgettext('checkin', 'Save status'));

        $tpl = $form->getTemplate();
        $tpl['PICK_COLOR'] = javascript('pick_color', array('input_id'=>'checkin-status_color'));
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'edit_status.tpl');
    }

    function postStatus()
    {
        if (empty($_POST['summary'])) {
            $this->message[] = dgettext('checkin', 'Missing a summary');
        } else {
            $this->status->setSummary($_POST['summary']);
        }

        if (empty($_POST['color'])) {
            $this->message[] = dgettext('checkin', 'Missing color code');
        } elseif (!$this->status->setColor($_POST['color'])) {
            $this->message[] = dgettext('checkin', 'Unacceptable color code');
        }

        $this->status->available = (int)$_POST['available'];

        if ($this->message) {
            return false;
        } else {
            return true;
        }
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
}

?>