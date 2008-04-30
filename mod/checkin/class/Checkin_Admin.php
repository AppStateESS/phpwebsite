<?php

/**
 * The administrative interface for Checkin
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Admin {
    var $title   = null;
    var $message = null;
    var $content = null;
    var $panel   = null;

    var $staff   = null;

    function Checkin_Admin()
    {
        $this->loadPanel();
    }

    function process()
    {
        if (isset($_REQUEST['aop'])) {
            $cmd = $_REQUEST['aop'];
        } elseif ($_REQUEST['tab']) {
            $cmd = $_REQUEST['tab'];
        } elseif (Current_User::allow('checkin', 'assign_visitors')) {
            $cmd = 'assign';
        } else {
            $cmd = 'waiting';
        }

        switch ($cmd) {
        case 'assign':
            $this->assign();
            break;

        case 'waiting':
            $this->waiting();
            break;
            
        case 'settings':
            $this->settings();
            break;
            
        case 'staff':
            $this->staff();
            break;

        case 'add_staff':
            $this->loadStaff();
            $this->addStaff();
            break;
        }

        Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
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
        $form->addText('username');

        $this->content = PHPWS_Template::process($tpl, 'checkin', 'add_staff.tpl');
    }

    function settings()
    {

    }

    function addStaffLink()
    {
        $vars['label'] = dgettext('checkin', 'Add staff member');
        $vars['address'] = PHPWS_Text::linkAddress('checkin', array('aop'=>'add_staff'), true);
        return javascript('open_window', $vars);
    }
    
    function loadStaff()
    {
        
    }
}

?>