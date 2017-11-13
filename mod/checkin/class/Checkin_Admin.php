<?php

/**
 * The administrative interface for Checkin
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
\phpws\PHPWS_Core::initModClass('checkin', 'Checkin.php');

class Checkin_Admin extends Checkin
{

    public $panel = null;
    public $use_panel = true;
    public $use_sidebar = true;
    public $current_staff = null;
    public $current_visitor = null;

    public function __construct()
    {
        $this->loadPanel();
    }

    public function process()
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
            \phpws\PHPWS_Core::errorPage('404');
        }

        $js = false;
        $js = isset($_GET['print']);
        switch ($cmd) {

            case 'finish_meeting':
                $this->finishMeeting();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'start_meeting':
                $this->startMeeting();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'sendback':
                $this->sendBack();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'unavailable':
                $this->unavailable();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'available':
                $this->available();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'report':
                if (!PHPWS_Settings::get('checkin', 'staff_see_reports') && !Current_User::allow('checkin', 'assign_visitors')) {
                    Current_User::disallow();
                }
                if (isset($_GET['daily_report'])) {
                    $this->dailyReport(isset($_GET['print']));
                } elseif (isset($_GET['summary_report'])) {
                    $this->summaryReport();
                } else {
                    $this->report();
                }
                //$this->report2();
                break;

            case 'daily_report':
                if (!PHPWS_Settings::get('checkin', 'staff_see_reports') && !Current_User::allow('checkin', 'assign_visitors')) {
                    Current_User::disallow();
                }
                break;

            case 'month_report':
                if (!Current_User::allow('checkin', 'assign_visitors')) {
                    Current_User::disallow();
                }

                $this->monthReport(isset($_GET['print']));
                break;

            case 'visitor_report':
                if (!Current_User::allow('checkin', 'assign_visitors')) {
                    Current_User::disallow();
                }
                $this->visitorReport(isset($_GET['print']));
                break;

            case 'reassign':
                // Called via ajax
                if (Current_User::authorized('checkin', 'assign_visitors')) {
                    if (isset($_GET['staff_id']) && $_GET['staff_id'] >= 0 && isset($_GET['visitor_id'])) {
                        $this->loadVisitor($_GET['visitor_id']);
                        $staff_id = $this->visitor->assigned;
                        $db = new PHPWS_DB('checkin_visitor');
                        $db->addValue('assigned', (int) $_GET['staff_id']);
                        $db->addWhere('id', (int) $_GET['visitor_id']);
                        PHPWS_Error::logIfError($db->update());
                        printf('staff_id %s, visitor_id %s', $_GET['staff_id'], $_GET['visitor_id']);
                        $this->loadStaff($staff_id);
                        /*
                          if ($this->staff->status == 3) {
                          $this->staff->status = 0;
                          $this->staff->save();
                          }
                         */
                    }
                }
                exit();
                break;

            case 'move_up':
                if (Current_User::allow('checkin', 'assign_visitors')) {
                    $db = new PHPWS_DB('checkin_staff');
                    $db->moveRow('view_order', 'id', $_GET['staff_id'], 'up');
                }
                \phpws\PHPWS_Core::goBack();
                break;

            case 'move_down':
                if (Current_User::allow('checkin', 'assign_visitors')) {
                    $db = new PHPWS_DB('checkin_staff');
                    $db->moveRow('view_order', 'id', $_GET['staff_id'], 'down');
                }
                \phpws\PHPWS_Core::goBack();
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
                \phpws\PHPWS_Core::goBack();
                break;

            case 'hide_panel':
                 \phpws\PHPWS_Cookie::write('checkin_hide_panel', 1);
                \phpws\PHPWS_Core::goBack();
                break;

            case 'show_panel':
                 \phpws\PHPWS_Cookie::delete('checkin_hide_panel');
                \phpws\PHPWS_Core::goBack();
                $this->panel->setCurrentTab('assign');
                $this->assign();
                break;

            case 'hide_sidebar':
                 \phpws\PHPWS_Cookie::write('checkin_hide_sidebar', 1);
                \phpws\PHPWS_Core::goBack();
                $this->panel->setCurrentTab('assign');
                $this->use_sidebar = false;
                $this->assign();
                break;

            case 'show_sidebar':
                 \phpws\PHPWS_Cookie::delete('checkin_hide_sidebar');
                \phpws\PHPWS_Core::goBack();
                $this->panel->setCurrentTab('assign');
                $this->assign();
                break;

            case 'waiting':
                $this->panel->setCurrentTab('waiting');
                $this->loadCurrentStaff();
                $this->waiting();
                break;

            case 'repeats':
                $this->repeats();
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
                \phpws\PHPWS_Core::goBack();
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
                        \phpws\PHPWS_Core::reroute('index.php?module=checkin&tab=reasons');
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
                    $this->staff->saveReasons();
                    \phpws\PHPWS_Core::reroute('index.php?module=checkin&tab=staff');
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

                \phpws\PHPWS_Core::reroute('index.php?module=checkin&tab=settings');
                break;

            case 'edit_reason':
                $this->loadReason();
                $this->editReason();
                break;

            case 'delete_reason':
                $this->loadReason();
                $this->reason->delete();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'deactivate_staff':
                \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');
                $staff = new Checkin_Staff($_GET['id']);
                $staff->active = 0;
                $staff->save();
                \phpws\PHPWS_Core::goBack();
                break;

            case 'activate_staff':
                \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');
                $staff = new Checkin_Staff($_GET['id']);
                $staff->active = 1;
                $staff->save();
                \phpws\PHPWS_Core::goBack();
                break;

            // This is for testing purposes and never happens in actual use
            case 'unassignAll':
                $this->unassignAll();
                break;

            // This is for testing purposes and never happens in actual use
            case 'auto_assign':
                $this->autoAssign();
                break;
        }

        if (empty($this->content)) {
            $this->content = 'Command not recognized.';
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

    public function loadPanel()
    {
        $link = 'index.php?module=checkin';

        $tabs['waiting'] = array('title' => 'Waiting',
            'link' => $link);

        if (Current_User::allow('checkin', 'assign_visitors')) {
            $tabs['assign'] = array('title' => 'Assignment',
                'link' => $link);
        }


        if (Current_User::allow('checkin', 'settings')) {
            $tabs['staff'] = array('title' => 'Staff',
                'link' => $link);

            $tabs['reasons'] = array('title' => 'Reasons',
                'link' => $link);

            $tabs['settings'] = array('title' => 'Settings',
                'link' => $link);
        }

        if (PHPWS_Settings::get('checkin', 'staff_see_reports') || Current_User::allow('checkin', 'assign_visitors')) {
            $tabs['report'] = array('title' => 'Report',
                'link' => $link);
        }


        $this->panel = new PHPWS_Panel('check-admin');
        $this->panel->quickSetTabs($tabs);
    }

    public function assign()
    {
        Layout::addStyle('checkin');
        javascriptMod('checkin', 'send_note');
        javascriptMod('checkin', 'reassign', array('authkey' => Current_User::getAuthKey()));
        $this->title = 'Assignment';
        $this->loadVisitorList(null, true);
        $this->loadStaffList(true);

        // id and name only for drop down menu
        $staff_list = $this->getStaffList(false, true, true);
        $staff_list = array_reverse($staff_list, true);
        $staff_list[0] = 'Unassigned';
        $staff_list[-1] = dgettext('checkin', '-- Move visitor --');
        $staff_list = array_reverse($staff_list, true);

        if (empty($this->staff_list)) {
            $this->content = 'No staff found.';
            return;
        }

        $status_list = $this->getStatusColors();
        // unassigned visitors

        $staff = new Checkin_Staff;
        $staff->display_name = 'Unassigned';

        $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
        $row['COLOR'] = '#ffffff';
        $row['DISPLAY_NAME'] = $staff->display_name;
        $tpl['rows'][] = $row;

        $count = 1;
        $backcount = -1;
        // Go through staff and list assignments
        foreach ($this->staff_list as $staff) {
            $row = array();
            $this->current_staff = & $staff;
            $row['VISITORS'] = $this->listVisitors($staff, $staff_list);
            $row['COLOR'] = $status_list[$staff->status];
            $row['DISPLAY_NAME'] = $staff->display_name;

            if (!isset($this->visitor_list[$staff->id])) {
                $this->current_visitor = null;
            } else {
                $this->current_visitor = & $this->visitor_list[$staff->id][0];
            }

            $this->statusButtons($row);
            if ($staff->status == 3) {
                $tpl['rows'][$backcount] = $row;
                $backcount--;
            } else {
                $tpl['rows'][$count] = $row;
                $count++;
            }
        }
        ksort($tpl['rows']);

        $tpl['VISITORS_LABEL'] = 'Visitors';
        $tpl['DISPLAY_NAME_LABEL'] = 'Staff name';
        $tpl['TIME_WAITING_LABEL'] = 'Time waiting';

        $tpl['HIDE_PANEL'] = $this->hidePanelLink();
        $tpl['HIDE_SIDEBAR'] = $this->hideSidebarLink();
        $tpl['REFRESH'] = sprintf('<a href="index.php?module=checkin&tab=assign">%s</a>', 'Refresh');
        // UNASSIGN_ALL and AUTO_ASSIGN are links for testing functionality of automatic visitor assignment.
        //$tpl['UNASSIGN_ALL'] = sprintf('<a href="index.php?module=checkin&aop=unassignAll">%s</a>', 'Unassign All'); // For testing purposes only
        //$tpl['AUTO_ASSIGN'] = sprintf('<a href="index.php?module=checkin&aop=auto_assign">%s</a>', 'Auto Assign');   // For testing purposes only

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'visitors.tpl');
        Layout::metaRoute('index.php?module=checkin&aop=assign', PHPWS_Settings::get('checkin', 'assign_refresh'));
    }

    /**
     * This method is for testing purposes only and is never called in real
     * world deployment.
     * This method unassigns all visitors from all staff members to allow them
     * to be automatically reassigned again.
     */
    public function unassignAll()
    {
        $db = new PHPWS_DB('checkin_visitor');
        $db->addValue('assigned', 0);
        $db->addWhere('finished', 0);
        $db->update();
        \phpws\PHPWS_Core::reroute('index.php?module=checkin&tab=assign');
    }

    /**
     * This method is for testing purposes inly and is never called in real
     * world deployment.
     * This method attemps to assign all unassigned visitors to a staff member.
     */
    public function autoAssign()
    {
        $db = new PHPWS_DB('checkin_visitor');
        $db->addWhere('assigned', 0);
        $visitors = $db->select('col');

        foreach ($visitors as $visitor) {
            $this->loadVisitor($visitor);
            $this->visitor->assign();
            $this->visitor->save();
        }
        \phpws\PHPWS_Core::reroute('index.php?module=checkin&tab=assign');
    }

    public function hideSidebarLink()
    {
        if (PHPWS_Cookie::read('checkin_hide_sidebar') || $this->use_sidebar == false) {
            $this->use_sidebar = false;
            return PHPWS_Text::moduleLink('Show sidebar', 'checkin', array('aop' => 'show_sidebar'));
        } else {
            return PHPWS_Text::moduleLink('Hide sidebar', 'checkin', array('aop' => 'hide_sidebar'));
        }
    }

    public function hidePanelLink()
    {
        if (PHPWS_Cookie::read('checkin_hide_panel') || $this->use_panel == false) {
            $this->use_panel = false;
            return PHPWS_Text::moduleLink('Show panel', 'checkin', array('aop' => 'show_panel'));
        } else {
            return PHPWS_Text::moduleLink('Hide panel', 'checkin', array('aop' => 'hide_panel'));
        }
    }

    public function listVisitors($staff, $staff_list)
    {
        if (empty($this->visitor_list[$staff->id])) {
            return 'No visitors waiting';
        }
        $vis_list = $this->visitor_list[$staff->id];
        unset($staff_list[$staff->id]);

        foreach ($vis_list as $vis) {
            $row['list'][] = $vis->row($staff_list, $staff);
        }

        $row['NAME_LABEL'] = dgettext('checkin', 'Name / Reason / Note');
        $row['WAITING_LABEL'] = dgettext('checkin', 'Time arrived/waiting');
        $row['ACTION_LABEL'] = 'Action';
        return PHPWS_Template::process($row, 'checkin', 'queue.tpl');
    }

    public function waiting($small_view = false)
    {
        Layout::addStyle('checkin');
        javascriptMod('checkin', 'send_note');
        $this->title = 'Waiting list';

        if (!$this->current_staff) {
            $this->content = 'You are not a staff member.';
            return;
        }

        // Load all visitors for this staff member
        $this->loadVisitorList($this->current_staff->id);

        // No visitors found, load all the visitors that are unassigned.
        if (empty($this->visitor_list)) {
            $tpl['MESSAGE'] = 'You currently do not have any visitors.';
            if (PHPWS_Settings::get('checkin', 'unassigned_seen')) {
                $this->loadVisitorList(0);
                if (!empty($this->visitor_list)) {
                    $this->unassigned_only = true;
                    $tpl['MESSAGE'] .= '<br />' . sprintf(dgettext('checkin', 'There are %s unassigned visitors.'), count($this->visitor_list));
                }
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
            $tpl['REDUCE'] = 'small-view';
            $tpl['CLOSE'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', 'Close');
            Layout::metaRoute('index.php?module=checkin&aop=small_wait', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        } else {
            $tpl['HIDE_PANEL'] = $this->hidePanelLink();
            $tpl['HIDE_SIDEBAR'] = $this->hideSidebarLink();
            $tpl['SMALL_VIEW'] = $this->smallViewLink();
            $tpl['REFRESH'] = sprintf('<a href="index.php?module=checkin&tab=waiting">%s</a>', 'Refresh');
            Layout::metaRoute('index.php?module=checkin&aop=waiting', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        }


        $tpl['NAME_LABEL'] = dgettext('checkin', 'Name / Notes');
        $tpl['WAITING_LABEL'] = 'Time waiting';
        $tpl['SENDBANK'] = 'what what';
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'waiting.tpl');
    }

    public function smallViewLink()
    {
        $vars['aop'] = 'small_wait';
        $js['address'] = PHPWS_Text::linkAddress('checkin', $vars, true);
        $js['label'] = 'Small view';
        $js['width'] = '640';
        $js['height'] = '480';
        return javascript('open_window', $js);
    }

    public function statusButtons(&$tpl)
    {
        $sendback = PHPWS_Settings::get('checkin', 'sendback');
        switch ($this->current_staff->status) {
            case 0:
                // Available
                if (!empty($this->visitor_list) && $this->current_visitor) {
                    $tpl['MEET'] = $this->startMeetingLink();
                    if ($sendback) {
                        $tpl['SENDBACK'] = $this->sendBackLink();
                    }
                }
                $tpl['UNAVAILABLE'] = $this->unavailableLink();
                $tpl['CURRENT_MEETING'] = 'You are currently available for meeting.';
                $tpl['CURRENT_CLASS'] = 'available';
                break;

            case 1:
                if ($this->current_visitor && $sendback) {
                    $tpl['SENDBACK'] = $this->sendBackLink();
                }
                $tpl['AVAILABLE'] = $this->availableLink();
                $tpl['CURRENT_MEETING'] = 'You are currently unavailable.';
                $tpl['CURRENT_CLASS'] = 'unavailable';
                break;

            case 2:
                $tpl['FINISH'] = $this->finishLink();
                $this->loadVisitor($this->current_staff->visitor_id);

                $tpl['CURRENT_MEETING'] = sprintf(dgettext('checkin', 'You are currently meeting with %s.'), $this->visitor->getName());
                $tpl['CURRENT_CLASS'] = 'meeting';
                break;

            case 3:
                // Send back
                if (!empty($this->visitor_list) && $this->current_visitor) {
                    $tpl['MEET'] = $this->startMeetingLink();
                }
                $tpl['AVAILABLE'] = $this->availableLink();
                $tpl['UNAVAILABLE'] = $this->unavailableLink();
                $tpl['CURRENT_MEETING'] = dgettext('checkin', sprintf('Waiting on %s to visit.', $this->current_visitor->firstname));
                $tpl['CURRENT_CLASS'] = 'sendback';
                break;
        }
    }

    public function finishLink()
    {
        $vars['aop'] = 'finish_meeting';
        $vars['visitor_id'] = $this->current_visitor->id;
        $vars['staff_id'] = $this->current_staff->id;
        $title = sprintf(dgettext('checkin', 'Finish meeting with %s %s'), $this->current_visitor->firstname, $this->current_visitor->lastname);
        return PHPWS_Text::secureLink($title, 'checkin', $vars, null, $title, 'finish-button action-button');
    }

    public function unavailableLink()
    {
        $vars['aop'] = 'unavailable';
        $vars['staff_id'] = $this->current_staff->id;
        return PHPWS_Text::secureLink('Make unavailable', 'checkin', $vars, null, 'Click to make unavailable for meeting', 'unavailable-button action-button');
    }

    public function sendBackLink()
    {
        $vars['aop'] = 'sendback';
        $vars['staff_id'] = $this->current_staff->id;
        return PHPWS_Text::secureLink('Send back', 'checkin', $vars, null, dgettext('checkin', sprintf('Click to send back %s for meeting', $this->current_visitor->firstname)), 'sendback-button action-button');
    }

    public function availableLink()
    {
        $vars['aop'] = 'available';
        $vars['staff_id'] = $this->current_staff->id;
        return PHPWS_Text::secureLink('Make available', 'checkin', $vars, null, 'Click to make available for meeting', 'available-button action-button');
    }

    public function startMeetingLink()
    {
        $vars['aop'] = 'start_meeting';
        $vars['staff_id'] = $this->current_staff->id;
        $vars['visitor_id'] = $this->current_visitor->id;
        if ($this->unassigned_only) {
            $title = sprintf(dgettext('checkin', 'Start meeting w/ %s (Unassigned)'), $this->current_visitor->getName());
        } else {
            $title = sprintf(dgettext('checkin', 'Start meeting w/ %s'), $this->current_visitor->getName());
        }
        return PHPWS_Text::secureLink($title, 'checkin', $vars, null, $title, 'meet-button action-button');
    }

    public function staff()
    {
        \phpws\PHPWS_Core::initCoreClass('DBPager.php');
        \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');

        $page_tags['ADD_STAFF'] = $this->addStaffLink();
        //$page_tags['STAFF_NOTE'] = dgettext('checkin', 'Note: Staff members with no filters will not have visitors automatically assigned to them.');
        $page_tags['FILTER_LABEL'] = 'Filter';

        $pager = new DBPager('checkin_staff', 'Checkin_Staff');
        $pager->setTemplate('staff.tpl');
        $pager->setModule('checkin');
        $pager->addRowTags('row_tags');
        $pager->setEmptyMessage('No staff found.');
        $pager->addPageTags($page_tags);
        $pager->joinResult('user_id', 'users', 'id', 'display_name');
        $pager->addSortHeader('lname_filter', 'Filter');
        $pager->addSortHeader('display_name', 'Staff name');
        $pager->addSortHeader('view_order', 'Order');

        $this->title = 'Staff';
        $this->content = $pager->get();
    }

    public function editStaff()
    {
        $form = new PHPWS_Form('edit-staff');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_staff');
        if (!$this->staff->id) {
            javascript('jquery');
            javascript('jquery_ui');
            $script_add = '<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/checkin/javascript/search_user/script.js"></script>';
            \Layout::addJSHeader($script_add);

            $this->title = 'Add staff member';
            $form->addText('username');
            $form->setLabel('username', 'Staff user name');
            $form->addSubmit('Add staff');
        } else {
            $this->title = 'Edit staff member';
            $form->addHidden('staff_id', $this->staff->id);
            $form->addTplTag('USERNAME', $this->staff->display_name);
            $form->addTplTag('USERNAME_LABEL', 'Staff user name');
            $form->addSubmit('Update staff');
        }

        // array of values to use with setMatch()
        $checks = array();
        $checks['last_name'] = $this->staff->filter_type & LAST_NAME_BITMASK ? 'yes' : 'no';
        $checks['reason'] = $this->staff->filter_type & REASON_BITMASK ? 'yes' : 'no';
        $checks['gender'] = $this->staff->filter_type & GENDER_BITMASK ? 'yes' : 'no';
        $checks['birthdate'] = $this->staff->filter_type & BIRTHDATE_BITMASK ? 'yes' : 'no';

        // Add checkbox for "Last Name" filter
        $form->addCheck('last_name', 'yes');
        $form->setMatch('last_name', $checks['last_name']);
        $form->setLabel('last_name', 'Last Name');
        $form->addText('last_name_filter', $this->staff->lname_filter);
        $form->setLabel('last_name_filter', dgettext('checkin', 'Example: a,b,ca-cf,d'));

        // Add checkbox for "Reasons" filter
        $reasons = $this->getReasons();
        if (!empty($reasons)) {
            $form->addCheck('reason', 'yes');
            $form->setMatch('reason', $checks['reason']);
            $form->setLabel('reason', 'Reason');
            $form->addMultiple('reason_filter', $reasons);
            $form->setMatch('reason_filter', $this->staff->_reasons);
        }

        // Add checkbox for "Gender" filter
        if (PHPWS_Settings::get('checkin', 'gender')) {
            $form->addCheck('gender', 'yes');
            $form->setMatch('gender', $checks['gender']);
            $form->setLabel('gender', 'Gender');
            $form->addSelect('gender_filter', array('male' => 'Male', 'female' => 'Female'));
            $form->setMatch('gender_filter', $this->staff->gender_filter);
        }

        // Add checkbox for "Birthdate" filter
        if (PHPWS_Settings::get('checkin', 'birthdate')) {
            javascript('datepicker');
            $form->addCheck('birthdate', 'yes');
            $form->setMatch('birthdate', $checks['birthdate']);
            $form->setLabel('birthdate', 'Birthdate');

            // Fill the date picker with the current filter start date if it is set
            if (isset($this->staff->birthdate_filter_start)) {
                $form->addText('start_date', date('m/d/Y', $this->staff->birthdate_filter_start));
            } else {
                $form->addText('start_date', date('m/d/Y'));
            }

            $form->setSize('start_date', 10);
            $form->setExtra('start_date', 'class="datepicker"');

            // Fill the date picker with the current filter end date if it is set
            if (isset($this->staff->birthdate_filter_end)) {
                $form->addText('end_date', date('m/d/Y', $this->staff->birthdate_filter_end));
            } else {
                $form->addText('end_date', date('m/d/Y'));
            }

            $form->setSize('end_date', 10);
            $form->setExtra('end_date', 'class="datepicker"');
        }

        $tpl = $form->getTemplate();
        $tpl['FILTER_LEGEND'] = 'Visitor filter';
        $tpl['STAFF_NOTE'] = dgettext('checkin', 'Note: Staff members with no filters will not have visitors automatically assigned to them.');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'edit_staff.tpl');
    }

    public function reasons()
    {
        \phpws\PHPWS_Core::initCoreClass('DBPager.php');
        \phpws\PHPWS_Core::initModClass('checkin', 'Reasons.php');

        $pt['MESSAGE_LABEL'] = 'Submission message';
        $pt['ADD_REASON'] = PHPWS_Text::secureLink('Add reason', 'checkin', array('aop' => 'edit_reason'));

        $pager = new DBPager('checkin_reasons', 'Checkin_Reasons');
        $pager->setModule('checkin');
        $pager->setTemplate('reasons.tpl');
        $pager->addPageTags($pt);
        $pager->addSortHeader('id', 'Id');
        $pager->addSortHeader('summary', 'Summary');
        $pager->addRowTags('rowTags');

        $this->title = 'Reasons';
        $this->content = $pager->get();
    }

    public function editReason()
    {
        $reason = & $this->reason;

        $form = new PHPWS_Form('edit-reason');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_reason');
        $form->addHidden('reason_id', $reason->id);

        $form->addText('summary', $reason->summary);
        $form->setSize('summary', 40);
        $form->setLabel('summary', 'Summary');
        $form->setRequired('summary');

        $form->addTextArea('message', $reason->message);
        $form->setRequired('message');
        $form->setLabel('message', 'Completion message');

        if ($reason->id) {
            $this->title = 'Update reason';
            $form->addSubmit('go', 'Update');
        } else {
            $this->title = 'Add new reason';
            $form->addSubmit('go', 'Add');
        }
        $template = $form->getTemplate();
        $this->content = PHPWS_Template::process($template, 'checkin', 'edit_reason.tpl');
    }

    public function settings()
    {
        $this->title = 'Settings';
        javascript('jquery');
        $form = new PHPWS_Form('settings');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_settings');
        $form->addCheck('front_page', 1);
        $form->setMatch('front_page', PHPWS_Settings::get('checkin', 'front_page'));
        $form->setLabel('front_page', dgettext('checkin', 'Show public sign-in on front page'));

        $form->addCheck('staff_see_reports', 1);
        $form->setMatch('staff_see_reports', PHPWS_Settings::get('checkin', 'staff_see_reports'));
        $form->setLabel('staff_see_reports', 'Staff can see reports');

        $form->addText('assign_refresh', PHPWS_Settings::get('checkin', 'assign_refresh'));
        $form->setSize('assign_refresh', '3');
        $form->setLabel('assign_refresh', dgettext('checkin', 'Assignment page refresh rate (in seconds)'));

        $form->addText('waiting_refresh', PHPWS_Settings::get('checkin', 'waiting_refresh'));
        $form->setSize('waiting_refresh', '3');
        $form->setLabel('waiting_refresh', dgettext('checkin', 'Waiting page refresh rate (in seconds)'));

        $form->addCheck('collapse_signin', 1);
        $form->setLabel('collapse_signin', 'Hide sidebar for visitors');
        $form->setMatch('collapse_signin', PHPWS_Settings::get('checkin', 'collapse_signin'));

        $form->addCheck('unassigned_seen', 1);
        $form->setLabel('unassigned_seen', 'Staff see unassigned visitors');
        $form->setMatch('unassigned_seen', PHPWS_Settings::get('checkin', 'unassigned_seen'));

        $form->addCheck('sendback', 1);
        $form->setLabel('sendback', 'Use send back button');
        $form->setMatch('sendback', PHPWS_Settings::get('checkin', 'sendback'));

        $form->addCheck('email', 1);
        $form->setLabel('email', 'Request email address');
        $form->setMatch('email', PHPWS_Settings::get('checkin', 'email'));

        // Checkbox for requesting gender when checking in
        $form->addCheck('gender', 1);
        $form->setLabel('gender', 'Request gender');
        $form->setMatch('gender', PHPWS_Settings::get('checkin', 'gender'));

        // Checkbox for requesting birthdate when checking in
        $form->addCheck('birthdate', 1);
        $form->setLabel('birthdate', 'Request date of birth');
        $form->setMatch('birthdate', PHPWS_Settings::get('checkin', 'birthdate'));

        $form->addSubmit('Save settings');
        $tpl = $form->getTemplate();

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'setting.tpl');
    }

    public function postSettings()
    {
        if (isset($_POST['add'])) {
            $reason = trim(strip_tags($_POST['new_reason']));
            if (!empty($reason)) {
                $this->addReason($reason);
            }
        }

        PHPWS_Settings::set('checkin', 'staff_see_reports', (int) isset($_POST['staff_see_reports']));
        PHPWS_Settings::set('checkin', 'unassigned_seen', (int) isset($_POST['unassigned_seen']));
        PHPWS_Settings::set('checkin', 'front_page', (int) isset($_POST['front_page']));
        PHPWS_Settings::set('checkin', 'collapse_signin', (int) isset($_POST['collapse_signin']));
        PHPWS_Settings::set('checkin', 'sendback', (int) isset($_POST['sendback']));
        PHPWS_Settings::set('checkin', 'email', (int) isset($_POST['email']));
        PHPWS_Settings::set('checkin', 'gender', (int) isset($_POST['gender']));
        PHPWS_Settings::set('checkin', 'birthdate', (int) isset($_POST['birthdate']));

        // If checkin does not ask for gender, make sure no staff member is filtering by gender
        if (!isset($_POST['gender'])) {
            $this->loadStaffList();
            foreach ($this->staff_list as $staff) {
                $staff->filter_type = $staff->filter_type & ~GENDER_BITMASK;
                $staff->save();
            }
        }

        // If checkin does not ask for birthdate, make sure no staff member is filtering by birthdate
        if (!isset($_POST['birthdate'])) {
            $this->loadstaffList();
            foreach ($this->staff_list as $staff) {
                $staff->filter_type = $staff->filter_type & ~BIRTHDATE_BITMASK;
                $staff->save();
            }
        }

        $seconds = (int) $_POST['assign_refresh'];
        if ($seconds < 1) {
            $seconds = 15;
        }
        PHPWS_Settings::set('checkin', 'assign_refresh', $seconds);

        $seconds = (int) $_POST['waiting_refresh'];
        if ($seconds < 1) {
            $seconds = 15;
        }
        PHPWS_Settings::set('checkin', 'waiting_refresh', $seconds);

        PHPWS_Settings::save('checkin');
    }

    public function addStaffLink()
    {
        return PHPWS_Text::secureLink('Add staff member', 'checkin', array('aop' => 'edit_staff'));
    }

    public function searchUsers()
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
        if (!empty($result)) {
            echo json_encode($result);
        } else {
            echo '[]';
        }
        exit();
    }

    public function addReason($reason)
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addValue('summary', $reason);
        return !PHPWS_Error::logIfError($db->insert());
    }

    /**
     * Removes a reason from the reason table
     */
    public function deleteReason($reason_id)
    {
        if (empty($reason_id) || !is_numeric($reason_id)) {
            return false;
        }

        $db = new PHPWS_DB('checkin_reasons');
        $db->addWhere('id', (int) $reason_id);
        return $db->delete();
    }

    public function updateReason()
    {
        if (empty($_GET['reason_id']) || empty($_GET['reason'])) {
            return;
        }

        $db = new PHPWS_DB('checkin_reasons');
        $db->addWhere('id', (int) $_GET['reason_id']);
        $db->addValue('summary', strip_tags($_GET['reason']));
        return !PHPWS_Error::logIfError($db->update());
    }

    public function postStaff()
    {
        $staff_id = 0;

        if (isset($_POST['staff_id'])) {
            $staff_id = (int) $_POST['staff_id'];
        }

        if (!empty($staff_id)) {
            $this->loadStaff($staff_id);
        } else {
            @$user_name = $_POST['username'];
            if (empty($user_name) || !Current_User::allowUsername($user_name)) {
                $this->message = 'Please try another user name';
                return false;
            }

            // Test user name, make sure exists
            $db = new PHPWS_DB('checkin_staff');
            $db->addWhere('user_id', 'users.id');
            $db->addWhere('users.username', $user_name);
            $db->addColumn('id');
            $result = $db->select('one');
            if (PHPWS_Error::logIfError($result)) {
                $this->message = 'Problem saving user.';
                return false;
            } elseif ($result) {
                $this->message = 'User already is staff member.';
                return false;
            }

            // user is allowed and new, get user_id to create staff
            $db = new PHPWS_DB('users');
            $db->addWhere('username', $user_name);
            $db->addColumn('id');
            $user_id = $db->select('one');
            if (PHPWS_Error::logIfError($result)) {
                $this->message = 'Problem saving user.';
                return false;
            }

            if (!$user_id) {
                $this->message = 'Could not locate anyone with this user name.';
                return false;
            }
            $this->loadStaff();
            $this->staff->user_id = $user_id;
        }

        // Blank filter to begin with
        $filter = 0x0;

        // Update last name filter
        if (isset($_POST['last_name'])) {
            $filter = $filter | LAST_NAME_BITMASK;
            if (!empty($_POST['last_name_filter'])) {
                $this->staff->filter_type = $filter;    // parseFilter() checks filter_type, so it needs to be updated early
                $this->staff->parseFilter($_POST['last_name_filter']);
            } else {
                $this->message[] = 'Please enter a last name filter.';
            }
        } else {
            $this->staff->lname_filter = null;
            $this->staff->lname_regexp = null;
        }

        // Update reason filter
        if (isset($_POST['reason'])) {
            $filter = $filter | REASON_BITMASK;
            if (!empty($_POST['reason_filter'])) {
                $this->staff->_reasons = $_POST['reason_filter'];
            } else {
                $this->message[] = 'Please pick one or more reasons.';
            }
        }

        // Update gender filter
        if (isset($_POST['gender'])) {
            $filter = $filter | GENDER_BITMASK;
            if (isset($_POST['gender_filter'])) {
                $this->staff->gender_filter = $_POST['gender_filter'];
            } else {
                $this->message[] = 'Please choose a gender filter.';
            }
        } else {
            $this->staff->gender_filter = null;
        }

        // Update birthdate filter
        if (isset($_POST['birthdate'])) {
            $filter = $filter | BIRTHDATE_BITMASK;
            if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
                $this->staff->birthdate_filter_start = strtotime($_POST['start_date']);
                $this->staff->birthdate_filter_end = strtotime($_POST['end_date']);
            } else {
                $this->message[] = 'Please enter a start and end date.';
            }
        } else {
            $this->staff->birthdate_filter_start = null;
            $this->staff->birthdate_filter_end = null;
        }

        // Update filter_type
        $this->staff->filter_type = $filter;

        return empty($this->message) ? true : false;
    }

    public function postReason()
    {
        $this->reason->summary = $_POST['summary'];
        $this->reason->message = $_POST['message'];
        if (empty($this->reason->summary)) {
            $this->message[] = 'Please enter the summary.';
        }

        if (empty($this->reason->message)) {
            $this->message[] = 'Please enter a completion message.';
        }

        return empty($this->message);
    }

    public function assignmentLink()
    {
        $vars['aop'] = 'assign';
        return PHPWS_Text::secureLink('Assignment page', 'checkin', $vars);
    }

    public function waitingLink()
    {
        $vars['aop'] = 'waiting';
        return PHPWS_Text::secureLink('Waiting page', 'checkin', $vars);
    }

    public function menu()
    {
        $tpl['WAITING'] = $this->waitingLink();
        $tpl['ASSIGN_PAGE'] = $this->assignmentLink();
        $tpl['TITLE'] = 'Checkin Menu';
        $content = PHPWS_Template::process($tpl, 'checkin', 'menu.tpl');
        Layout::add($content, 'checkin', 'checkin-admin');
    }

    public function loadCurrentStaff()
    {
        \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');
        if (empty($this->current_staff)) {
            $db = new PHPWS_DB('checkin_staff');
            $db->addWhere('user_id', Current_User::getId());
            $db->addColumn('id');
            $id = $db->select('one');

            $staff = new Checkin_Staff($id);
            if ($staff->id) {
                $this->current_staff = & $staff;
            }
        }
    }

    public function saveNote()
    {
        $this->visitor->note = strip_tags(trim($_POST['note_body']));
        PHPWS_Error::logIfError($this->visitor->save());
    }

    public function startMeeting()
    {
        $this->loadStaff();
        $this->loadVisitor();

        // set staff to meeting status and with current visitor
        $this->staff->status = 2;
        $this->staff->visitor_id = $this->visitor->id;
        $this->staff->save();

        $this->visitor->start_meeting = time();
        $this->visitor->assigned = $this->staff->id;
        $this->visitor->save();
    }

    public function finishMeeting()
    {
        $this->loadStaff();
        $this->loadVisitor();

        // set staff to meeting status and with current visitor
        $this->staff->status = 0;
        $this->staff->visitor_id = 0;
        $this->staff->save();

        $this->visitor->end_meeting = time();
        $this->visitor->finished = true;
        $this->visitor->save();
    }

    public function unavailable()
    {
        $this->loadStaff();
        $this->staff->status = 1;
        $this->staff->save();
    }

    public function available()
    {
        $this->loadStaff();
        $this->staff->status = 0;
        $this->staff->save();
    }

    public function sendback()
    {
        $this->loadStaff();
        $this->staff->status = 3;
        $this->staff->save();
    }

    public function visitorReport($print = false)
    {
        \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');
        \phpws\PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $visitor = new Checkin_Visitor($_GET['vis_id']);
        if (!$visitor->id) {
            $this->content = 'Visitor not found';
            return;
        }

        $db = new PHPWS_DB('checkin_visitor');
        $db->addWhere('firstname', strtolower($visitor->firstname) . '%', 'like');
        $db->addWhere('lastname', strtolower($visitor->lastname), 'like');
        $db->addOrder('arrival_time');
        $result = $db->getObjects('Checkin_Visitor');
        if (empty($result)) {
            $this->content = 'Visitor not found';
            return;
        }
        $count = $total_wait = $total_spent = 0;
        $reasons = $this->getReasons();
        $staff_list = array();
        foreach ($result as $vis) {
            if (isset($staff_list[$vis->assigned])) {
                $staff = $staff_list[$vis->assigned];
            } else {
                $staff_list[$vis->assigned] = $staff = new Checkin_Staff($vis->assigned);
            }
            $row = array();
            $wait = $vis->start_meeting - $vis->arrival_time;
            $spent = $vis->end_meeting - $vis->start_meeting;
            $day = strftime('%A, %e %b', $vis->arrival_time);
            $row = (array('REASON' => $reasons[$vis->reason],
                'ARRIVAL' => strftime('%c', $vis->arrival_time),
                'NOTE' => $vis->note,
                'WAITED' => Checkin::timeWaiting($wait),
                'SPENT' => Checkin::timeWaiting($spent),
                'STAFF' => $staff->display_name));
            if ($spent >= 0) {
                $count++;
                $total_wait += $wait;
                $total_spent += $spent;
            }
            $tpl['visitors'][] = $row;
        }

        if ($count >= 1) {
            $average_wait = floor($total_wait / $count);
        } else {
            $average_wait = 0;
        }

        $tpl['STAFF_LABEL'] = 'Staff';
        $tpl['REASON_LABEL'] = dgettext('checkin', 'Reason/Note');
        $tpl['WAITED_LABEL'] = 'Waited';
        $tpl['SPENT_LABEL'] = 'Visited';
        $tpl['ARRIVAL_LABEL'] = 'Arrived';
        $tpl['PRINT_LINK'] = PHPWS_Text::secureLink('Print view', 'checkin', array('aop' => 'visitor_report', 'print' => 1, 'vis_id' => $visitor->id));

        $tpl['NAME_NOTE'] = dgettext('checkin', 'Please note: if a visitor typed in a different or misspelled name, they may not appear on this list. Also, different people may have the same name.');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'visitor_report.tpl');
        $this->title = sprintf('Visits from ' . $visitor->getName());
    }

    public function monthReport($print = false)
    {
        \phpws\PHPWS_Core::initModClass('checkin', 'Staff.php');
        \phpws\PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $staff = new Checkin_Staff((int) $_GET['staff_id']);
        if (!$staff->id) {
            $this->content = 'Staff member not found.';
        }

        $date = (int) $_GET['date'];
        $db = new PHPWS_DB('checkin_visitor');

        $start_date = mktime(0, 0, 0, date('m', $date), 1, date('Y', $date));
        $end_date = mktime(0, -1, 0, date('m', $date) + 1, 1, date('Y', $date));
        $this->title = sprintf('%s - Visitors seen in %s', $staff->display_name, strftime('%b, %Y', $start_date));
        $db->addWhere('assigned', $staff->id);
        $db->addWhere('finished', 1);
        $db->addWhere('start_meeting', $start_date, '>=');
        $db->addWhere('end_meeting', $end_date, '<=');
        $db->addOrder('start_meeting');
        $visitors = $db->getObjects('Checkin_Visitor');
        $count = 0;
        if (empty($visitors)) {
            $this->content = 'This staff member did not meet with any visitors this month.';
            return;
        } else {
            $total_wait = $total_spent = 0;
            $reasons = $this->getReasons();
            $track_day = null;
            foreach ($visitors as $vis) {
                $row = array();
                $wait = $vis->start_meeting - $vis->arrival_time;
                $spent = $vis->end_meeting - $vis->start_meeting;
                $day = strftime('%A, %e %b', $vis->arrival_time);

                $row = (array('VIS_NAME' => PHPWS_Text::moduleLink($vis->getName(), 'checkin', array('aop' => 'visitor_report', 'vis_id' => $vis->id)),
                    'REASON' => $reasons[$vis->reason],
                    'ARRIVAL' => strftime('%r', $vis->arrival_time),
                    'NOTE' => $vis->note,
                    'WAITED' => Checkin::timeWaiting($wait),
                    'SPENT' => Checkin::timeWaiting($spent)));

                if ($track_day != $day) {
                    $track_day = $day;
                    $row['DATE'] = $track_day;
                }
                if ($spent >= 0) {
                    $count++;
                    $total_wait += $wait;
                    $total_spent += $spent;
                }
                $tpl['visitors'][] = $row;
            }

            //prevent divide by zero
            if ($count >= 1) {
                $average_wait = floor($total_wait / $count);
            } else {
                $average_wait = 0;
            }
            $tpl['TOTAL_SPENT'] = sprintf(dgettext('checkin', 'Total time in meeting: %s'), Checkin::timeWaiting($total_spent));
            $tpl['TOTAL_WAIT'] = sprintf(dgettext('checkin', 'Total wait time: %s'), Checkin::timeWaiting($total_wait));
            $tpl['AVERAGE_WAIT'] = sprintf(dgettext('checkin', 'Average wait time: %s'), Checkin::timeWaiting($average_wait));
        }

        $tpl['VISITORS_SEEN'] = sprintf(dgettext('checkin', 'Visitors seen: %s'), $count);

        $tpl['NAME_LABEL'] = dgettext('checkin', 'Name, Reason, & Note');
        $tpl['WAITED_LABEL'] = 'Waited';
        $tpl['SPENT_LABEL'] = 'Visited';
        $tpl['ARRIVAL_LABEL'] = 'Arrived';
        $tpl['PRINT_LINK'] = PHPWS_Text::secureLink('Print view', 'checkin', array('aop' => 'month_report', 'print' => '1', 'staff_id' => $staff->id, 'date' => $start_date));
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'monthly_report.tpl');
    }

    public function report2()
    {
        $today = mktime(0, 0, 0);
        $tomorrow = $today + 86400;
        $form = new PHPWS_Form('report-date');
        $form->setMethod('get');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'report');

        // Single day report
        $form->addTplTag('DAY_LABEL', 'All visits on');
        $form->addText('day_date', strftime('%m/%d/%Y', $today));
        $form->setExtra('day_date', 'class="datepicker"');
        $form->setSize('day_date', 10);
        $form->addSubmit('day_submit', 'View Report');

        // Timespan report
        $form->addTplTag('TIMESPAN_LABEL', 'All visits between');
        $form->addText('timespan_start', strftime('%m/%d/%Y', $today));
        $form->setExtra('timespan_start', 'class="datepicker"');
        $form->setSize('timespan_start', 10);
        $form->addText('timespan_end', strftime('%m/%d/%Y', $tomorrow));
        $form->setExtra('timespan_end', 'class="datepicker"');
        $form->setSize('timespan_end', 10);
        $form->addSubmit('timespan_submit', 'View Report');

        // Single visitor report
        $form->addTplTag('VISITOR_LABEL', 'All visits by');
        $form->addText('visitor_name');
        $form->addSubmit('visitor_submit', 'View Report');

        // All visitor report
        $form->addTplTag('ALL_VISITORS_LABEL', 'All visits by all visitors');
        $form->addSubmit('all_visitors_submit', 'View Report');

        // Reason report
        $reasons = $this->getReasons();

        if (!empty($reasons)) {
            $reasons = array_reverse($reasons, true);
            $reasons[0] = dgettext('checkin', '-- Choose a reason --');
            $reasons = array_reverse($reasons, true);

            $form->addSelect('reason_select', $reasons);
            $form->addTplTag('REASON_LABEL', 'All visits for');
        }
        $form->addSubmit('reason_submit', 'View Report');

        $tpl = $form->getTemplate();
        javascript('datepicker');

        //$this->content = PHPWS_Template::process($tpl, 'checkin', 'report.tpl');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'report_new.tpl');
    }

    public function report()
    {
        $udate = mktime(0, 0, 0);
        $week_end = $udate + 86400;
        $current_date = strftime('%m/%d/%Y', $udate);
        $form = new PHPWS_Form('report-date');
        $form->setMethod('get');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'report');
        $form->addText('cdate', $current_date);
        $form->setExtra('cdate', 'class="datepicker"');
        $form->setLabel('cdate', 'Visitor day');
        $form->addSubmit('daily_report', 'Daily visit summary');
        $form->setSize('cdate', 10);


        $form->addText('start_date', $current_date);
        $form->setLabel('start_date', 'Start date');
        $form->setSize('start_date', 10);
        $form->setExtra('start_date', 'class="datepicker"');
        $form->addText('end_date', strftime('%m/%d/%Y', $week_end));
        $form->setLabel('end_date', 'End date');
        $form->setSize('end_date', 10);
        $form->setExtra('end_date', 'class="datepicker"');

        $name = isset($_GET['visitor_name']) ? $_GET['visitor_name'] : null;

        $form->addText('visitor_name', $name);
        $form->setLabel('visitor_name', 'Visitor name');
        $form->addSubmit('summary_report', 'Summary report');


        $tpl = $form->getTemplate();
        javascript('datepicker');


        $tpl['PRINT_LINK'] = PHPWS_Text::secureLink('Print view', 'checkin', array('aop' => 'report', 'print' => 1, 'udate' => $udate));
        $tpl['REPEAT_VISITS'] = PHPWS_Text::moduleLink('Repeat visits', 'checkin', array('aop' => 'repeats', 'date' => $udate));

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'report.tpl');
    }

    public function dailyReport($print = false)
    {
        \phpws\PHPWS_Core::initCoreClass('Link.php');
        $this->loadStaffList();
        if (empty($this->staff_list)) {
            $this->content = 'No staff have been created.';
            return;
        }
        $tpl = array();

        if (isset($_GET['udate'])) {
            $udate = (int) $_GET['udate'];
        } elseif (isset($_GET['cdate'])) {
            $udate = strtotime($_GET['cdate']);
        } else {
            $udate = mktime(0, 0, 0);
        }
        $current_date = strftime('%m/%d/%Y', $udate);

        $this->title = sprintf(dgettext('checkin', 'Report for %s'), strftime('%e %B, %Y', $udate));

        if (!$print) {
            $form = new PHPWS_Form('report-date');
            $form->setMethod('get');
            $form->addHidden('module', 'checkin');
            $form->addText('cdate', $current_date);
            $form->setExtra('cdate', 'class="datepicker"');
            $form->addHidden('aop', 'report');
            $form->setLabel('cdate', 'Date');
            $form->addSubmit('daily_report', 'Go');
            $tpl = $form->getTemplate();
            javascript('datepicker');


            $tpl['PRINT_LINK'] = PHPWS_Text::secureLink('Print view', 'checkin', array('aop' => 'report', 'print' => 1, 'udate' => $udate, 'daily_report' => 1));
            $tpl['REPEAT_VISITS'] = PHPWS_Text::moduleLink('Repeat visits', 'checkin', array('aop' => 'repeats', 'date' => $udate));
        }

        $tObj = new PHPWS_Template('checkin');
        $tObj->setFile('daily_report.tpl');

        $this->loadStaffList();
        $reasons = $this->getReasons();
        if (empty($reasons)) {
            $reasons[0] = 'No reason';
        }

        \phpws\PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $db = new PHPWS_DB('checkin_visitor');
        $db->addWhere('start_meeting', $udate, '>=');
        $db->addWhere('end_meeting', $udate + 86400, '<');
        $db->addWhere('finished', 1);
        $db->setIndexBy('assigned', true);
        $visitors = $db->getObjects('Checkin_Visitor');


        foreach ($this->staff_list as $staff) {
            $row = array();
            $row['NAME_LABEL'] = dgettext('checkin', 'Name, Reason, & Note');
            $row['WAITED_LABEL'] = 'Waited';
            $row['SPENT_LABEL'] = 'Visited';
            $row['ARRIVAL_LABEL'] = 'Arrived';
            $average_wait = $total_wait = $count = $total_spent = $total_visit = 0;
            if (isset($visitors[$staff->id])) {
                foreach ($visitors[$staff->id] as $vis) {
                    $wait = $vis->start_meeting - $vis->arrival_time;
                    $spent = $vis->end_meeting - $vis->start_meeting;

                    if (isset($reasons[$vis->reason])) {
                        $reason = $reasons[$vis->reason];
                    } else {
                        $reason = '<em>' . 'System missing reason' . '</em>';
                    }

                    $tObj->setCurrentBlock('subrow');
                    $data = array('VIS_NAME' => PHPWS_Text::moduleLink($vis->getName(), 'checkin', array('aop' => 'visitor_report', 'vis_id' => $vis->id)),
                        'REASON' => $reason,
                        'ARRIVAL' => strftime('%r', $vis->arrival_time),
                        'NOTE' => $vis->note,
                        'WAITED' => Checkin::timeWaiting($wait),
                        'SPENT' => Checkin::timeWaiting($spent));

                    if (!empty($vis->email)) {
                        $data['EMAIL'] = '<a href="mailto:' . $vis->email . '">' . $vis->email . '</a>';
                    }

                    $tObj->setData($data);
                    $tObj->parseCurrentBlock();
                    if ($spent >= 0) {
                        $count++;
                        $total_wait += $wait;
                        $total_spent += $spent;
                    }
                }
                //prevent divide by zero
                if ($count >= 1) {
                    $average_wait = floor($total_wait / $count);
                } else {
                    $average_wait = 0;
                }
            } else {
                $tObj->setCurrentBlock('message');
                $tObj->setData(array('NOBODY' => 'No visitors seen'));
                $tObj->parseCurrentBlock();
            }

            $tObj->setCurrentBlock('row');
            $link = new PHPWS_Link($staff->display_name, 'checkin', array('aop' => 'month_report', 'staff_id' => $staff->id, 'date' => $udate), true);
            $link->setTitle('See monthly totals');
            $row['DISPLAY_NAME'] = $link->get();
            $row['VISITORS_SEEN'] = sprintf(dgettext('checkin', 'Visitors seen: %s'), $count);
            if ($count) {
                $row['TOTAL_SPENT'] = sprintf(dgettext('checkin', 'Total time in meeting: %s'), Checkin::timeWaiting($total_spent));
                $row['TOTAL_WAIT'] = sprintf(dgettext('checkin', 'Total wait time: %s'), Checkin::timeWaiting($total_wait));
                $row['AVERAGE_WAIT'] = sprintf(dgettext('checkin', 'Average wait time: %s'), Checkin::timeWaiting($average_wait));
            }

            $tObj->setData($row);
            $tObj->parseCurrentBlock();
        }

        $start_date = mktime(0, 0, 0, date('m', $udate), 1, date('Y', $udate));
        $end_date = mktime(0, -1, 0, date('m', $udate) + 1, 1, date('Y', $udate));

        $tObj->setData($tpl);
        $this->content = $tObj->get();
    }

    public function removeVisitor()
    {
        $this->loadVisitor();
        $this->visitor->delete();
    }

    public function repeats()
    {
        \phpws\PHPWS_Core::initCoreClass('DB2.php');
        $end_date = (int) $_GET['date'];
        $start_date = mktime(0, 0, 0, date('m', $end_date) - 1, date('d', $end_date));
        $this->title = sprintf(dgettext('checkin', 'Multiple visits made between %s and %s'), strftime('%b %e', $start_date), strftime('%b %e', $end_date));

        $limit = 2;

        if (isset($_GET['visit_query'])) {
            $limit = (int) $_GET['visit_query'];
        }

        if ($limit > 10 || $limit < 2) {
            $limit = 2;
        }

        try {
            $sub = new DB2;
            $t1 = $sub->addTable('checkin_visitor', 't1');
            $t1_id = $t1->addField('id');
            $exp = $sub->addExpression('COUNT(' . $t1_id->__toString() . ')', 'num');
            $t1_fn = $t1->addField('firstname');
            $t1_ln = $t1->addField('lastname');
            $t1->addWhere('arrival_time', $start_date, '>=');
            $t1->addWhere('arrival_time', $end_date, '<=');
            $t1->addOrderBy('lastname');
            $sub->setGroupBy(array($t1_fn, $t1_ln));

            $db2 = new DB2;
            $t2 = $db2->addSubSelect($sub, 't2');
            $t2->addWhere($exp, $limit, '>=');
            $result = $db2->select();
        } catch (PEAR_Exception $e) {
            $this->content = 'Sorry an error occurred.';
            $db2->logError($e);
            return;
        }
        if (empty($result)) {
            $this->content = 'No repeat visits within the last month.';
            return;
        }

        $form = new PHPWS_Form;
        $form->setMethod('get');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'repeats');
        $form->addHidden('date', $end_date);
        $form->addSelect('visit_query', array(2 => 2, 4 => 4, 6 => 6, 8 => 8, 10 => 10));
        $form->setMatch('visit_query', $limit);
        $form->addSubmit('go', 'Visits greater to or equal to');

        $tpl = $form->getTemplate();

        foreach ($result as $visit) {
            $rows['NAME'] = PHPWS_Text::moduleLink(sprintf('%s, %s', $visit['lastname'], $visit['firstname']), 'checkin', array('aop' => 'visitor_report', 'vis_id' => $visit['id']));
            $rows['VISITS'] = $visit['num'];
            $tpl['visitors'][] = $rows;
        }

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'repeats.tpl');
    }

    private function summaryReport()
    {
        javascript('datepicker');

        $form = new PHPWS_Form('report-date');
        $form->setMethod('get');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'report');
        $form->addHidden('summary_report', 1);

        $form->addText('start_date', $_GET['start_date']);
        $form->setLabel('start_date', 'Start date');
        $form->setSize('start_date', 10);
        $form->setExtra('start_date', 'class="datepicker"');
        $form->addText('end_date', $_GET['end_date']);
        $form->setLabel('end_date', 'End date');
        $form->setSize('end_date', 10);
        $form->setExtra('end_date', 'class="datepicker"');

        if (!empty($_GET['visitor_name'])) {
            $name = trim(strip_tags($_GET['visitor_name']));
        } else {
            $name = null;
        }
        $form->addText('visitor_name', $name);
        $form->setLabel('visitor_name', 'Visitor name');
        $form->addSubmit('Summary report');

        $db = new PHPWS_DB('checkin_staff');
        $db->addColumn('checkin_staff.id');
        $db->addColumn('users.display_name');
        $db->addWhere('checkin_staff.user_id', 'users.id');
        $db->setIndexBy('id');
        $db->addOrder('users.display_name desc');
        $assigned = $db->select('col');
        $assigned[0] = 'Show all';
        $assigned = array_reverse($assigned, true);

        $form->addSelect('assigned', $assigned);
        $form->setLabel('assigned', 'By staff');
        if (isset($_GET['assigned'])) {
            $staff_id = (int) $_GET['assigned'];
            $form->setMatch('assigned', $staff_id);
        } else {
            $staff_id = 0;
        }

        $tpl = $form->getTemplate();

        $start_date = strtotime($_GET['start_date']);
        $end_date = strtotime($_GET['end_date']);


        if (empty($start_date) || empty($end_date) || $start_date > $end_date) {
            $tpl['EMPTY'] = 'Please enter your date range again.';
        } else {
            $this->title = 'Visitors from ' . $_GET['start_date'] . ' to ' . $_GET['end_date'];

            $db = new PHPWS_DB('checkin_visitor');
            $db->addWhere('arrival_time', $start_date, '>=');
            $db->addWhere('arrival_time', $end_date, '<=');
            $db->addColumn('id');
            $db->addColumn('arrival_time');
            $db->addColumn('firstname');
            $db->addColumn('lastname');
            $db->addColumn('start_meeting');
            $db->addColumn('end_meeting');
            if ($staff_id) {
                $db->addWhere('assigned', $staff_id);
            }


            if (!empty($name)) {
                $name = strtolower($name);
                if (strlen($name) == 1) {
                    $db->addWhere('firstname', "$name%", 'like', 'and', 'name');
                    $db->addWhere('lastname', "$name%", 'like', 'or', 'name');
                } else {
                    $db->addWhere('firstname', "%$name%", 'like', 'and', 'name');
                    $db->addWhere('lastname', "%$name%", 'like', 'or', 'name');
                }
            }

            $result = $db->select();

            $total_visits = 0;
            $total_wait = 0;
            $total_meeting = 0;
            $total_days = 0;

            $incomplete_visits = 0;
            $current_day = null;
            foreach ($result as $visit) {
                extract($visit);
                $arrival_day = date('MdY', $arrival_time);
                if ($current_day != $arrival_day) {
                    $current_day = $arrival_day;
                    $total_days++;
                }
                $row = array();
                if (!$start_meeting || !$end_meeting) {
                    $incomplete_visits++;
                    continue;
                }

                $total_visits++;
                $twaited = $start_meeting - $arrival_time;
                $waited = Checkin::timeWaiting($twaited);

                if ($end_meeting) {
                    $tmeeting = $end_meeting - $start_meeting;
                }
                $meeting = Checkin::timeWaiting($tmeeting);

                $row['VISIT'] = $total_visits;
                $row['VISITOR'] = "$firstname $lastname";
                $row['DATE'] = date('g:ia m.d.Y', $arrival_time);
                $row['WAITED'] = $waited;
                $row['MEETING'] = $meeting;
                $tpl['rows'][] = $row;
                $total_wait += $twaited;
                $total_meeting += $tmeeting;
            }
            if ($total_visits) {
                $tpl['TOTAL_DAYS'] = $total_days;
                $tpl['TOTAL_VISITS'] = $total_visits;
                $tpl['AVG_VISITS'] = round($total_visits / $total_days, 1);
                $tpl['TOTAL_WAIT'] = Checkin::timeWaiting($total_wait);
                $tpl['TOTAL_MEETING'] = Checkin::timeWaiting($total_meeting);
                $tpl['AVG_WAIT'] = Checkin::timeWaiting(round($total_wait / $total_visits));
                $tpl['AVG_MEETING'] = Checkin::timeWaiting(round($total_meeting / $total_visits));
                $tpl['INCOMPLETE_MEETINGS'] = $incomplete_visits;
            } else {
                $tpl['EMPTY'] = 'No visits made in this date range.';
            }
        }
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'summary_report.tpl');
    }

}
