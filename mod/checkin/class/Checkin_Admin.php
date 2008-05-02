<?php

/**
 * The administrative interface for Checkin
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Admin extends Checkin {
    var $panel   = null;


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
            $this->panel->setCurrentTab('settings');
            $this->settings();
            break;
            
        case 'staff':
            $this->panel->setCurrentTab('staff');
            $this->staff();
            break;

        case 'add_staff':
            $this->loadStaff();
            $this->editStaff();
            break;

        case 'search_users':
            $this->searchUsers();
            break;
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

        $page_tags['ADD_STAFF'] = $this->addStaffLink();

        $pager = new DBPager('checkin_staff', 'Checkin_Staff');
        $pager->setTemplate('staff.tpl');
        $pager->setModule('checkin');
        $pager->setEmptyMessage(dgettext('checkin', 'No staff found.'));
        $pager->addPageTags($page_tags);

        $this->title = dgettext('checkin', 'Staff');
        $this->content = $pager->get();
    }

    function editStaff()
    {
        $form = new PHPWS_Form('edit-staff');
        $form->addHidden('module', 'checkin');
        if (!$this->staff->user_id) {
            javascript('jquery');
            javascript('modules/checkin/search_user');

            $this->title = dgettext('checkin', 'Add staff member');
            $form->addText('username');
            $form->setLabel('username', dgettext('checkin', 'Staff user name'));
        } else {
            $this->title = dgettext('checkin', 'Edit staff member');
            $form->addTplTag('USERNAME', $this->staff->_display_name);
            $form->addTplTag('USERNAME_LABEL', dgettext('checkin', 'Staff user name'));
        }
        
        $form->addRadioAssoc('filter_type', array('none'     =>dgettext('checkin', 'None'),
                                                  'last_name'=>dgettext('checkin', 'Last name'),
                                                  'reason'   =>dgettext('checkin', 'Reason')));
        $form->setMatch('filter_type', 'none');

        $form->addText('last_name_filter', $this->staff->getFilter());
        $form->setLabel('last_name_filter', dgettext('checkin', 'Example: a,b,ca-cf,d'));

        $reasons = $this->getReasons();

        if (empty($reasons)) {
            $form->addTplTag('REASONS', dgettext('checkin', 'No reasons found.'));
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
}

?>