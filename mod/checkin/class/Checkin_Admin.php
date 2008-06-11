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
    var $panel   = null;

    function Checkin_Admin()
    {
        $this->loadPanel();
        Layout::collapse();
    }

    function process()
    {
        if (!Current_User::allow('checkin')) {
            Current_User::disallow();
        }

        if (isset($_REQUEST['aop'])) {
            $cmd = $_REQUEST['aop'];
        } elseif ($_REQUEST['tab']) {
            $cmd = $_REQUEST['tab'];
        } elseif (Current_User::allow('checkin', 'assign_visitors')) {
            $cmd = 'assign';
        } else {
            $cmd = 'waiting';
        }

        $js = false;

        switch ($cmd) {
        case 'assign':
            $this->panel->setCurrentTab('assign');
            $this->assign();
            break;

        case 'waiting':
            $this->panel->setCurrentTab('waiting');
            $this->waiting();
            break;
            
        case 'settings':
            if (Current_User::allow('checkin', 'settings')) {
                $this->panel->setCurrentTab('settings');
                $this->settings();
            }
            break;
            
        case 'staff':
            $this->panel->setCurrentTab('staff');
            $this->staff();
            break;

        case 'add_staff':
            $this->loadStaff();
            $this->editStaff();
            break;

        case 'edit_staff':
            $this->loadStaff();
            $this->editStaff();
            break;

        case 'search_users':
            $this->searchUsers();
            break;

        case 'update_reason':
            if (Current_User::authorized('checkin', 'settings')) {
                $this->updateReason();
            }
            $this->panel->setCurrentTab('settings');
            $this->settings();
            break;

        case 'post_staff':
            if (Current_User::authorized('checkin', 'edit_staff')) {
                if ($this->postStaff()) {
                    $this->staff->save();
                    PHPWS_Core::reroute('index.php?module=checkin&tab=settings');
                    // save post
                } else {
                    // post failed
                    $this->loadStaff();
                    $this->editStaff();
                }
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
            
        }

        if (empty($this->content)) {
            $this->content = dgettext('checkin', 'Command not recognized.');
        }

        if ($js) {
            Layout::nakedDisplay($this->content, $this->title);
        } else {
            Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
        }
    }

    function loadPanel()
    {
        $link = 'index.php?module=checkin';

        $tabs['waiting'] = array('title'=>dgettext('checkin', 'Waiting'),
                                 'link'=>$link);

        $tabs['staff'] =  array('title'=>dgettext('checkin', 'Staff'),
                                 'link'=>$link);

        if (Current_User::allow('checkin', 'assign_filters')) {
            $tabs['assign'] = array('title'=>dgettext('checkin', 'Assignment'),
                                    'link'=>$link);
        }

        if (Current_User::allow('checking', 'settings')) {
            $tabs['settings'] = array('title'=>dgettext('checkin', 'Settings'),
                                      'link'=>$link);
        }


        $this->panel = new PHPWS_Panel('check-admin');
        $this->panel->quickSetTabs($tabs);
    }

    function assign()
    {

    }

    function waiting()
    {

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

    function settings()
    {
        javascript('jquery');
        $form = new PHPWS_Form('reasons');
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_settings');
        $form->addSubmit('add', dgettext('checkin', 'Add reason'));
        $form->addText('new_reason');
        $form->setLabel('new_reason', dgettext('checkin', 'Enter new reason'));
        $form->setSize('new_reason', 40, 100);
        $form->addCheck('front_page', 1);
        $form->setMatch('front_page', PHPWS_Settings::get('checkin', 'front_page'));
        $form->setLabel('front_page', dgettext('checkin', 'Show public sign-in on front page'));
        $reasons = $this->getReasons();
        if (!empty($reasons)) {
            $form->addTplTag('EDIT', javascript('modules/checkin/edit_reason', array('question'=> dgettext('checkin', 'Update reason'),
                                                                                     'address' => 'index.php?module=checkin&aop=update_reason&authkey=' . Current_User::getAuthKey(),
                                                                                     'label' => dgettext('checkin', 'Edit'))));
            $form->addSubmit('delete', dgettext('checkin', 'Delete'));
            $form->addSelect('edit_reason', $reasons);
        }

        $form->addSubmit('default', dgettext('checkin', 'Save'));

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

    function addStaffLink()
    {
        return PHPWS_Text::secureLink(dgettext('checkin', 'Add staff member'), 'checkin', array('aop'=>'add_staff'));
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

}

?>