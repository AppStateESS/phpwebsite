<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireInc('rideboard', 'defines.php');

class Rideboard {
    var $ride    = null;
    var $panel   = null;
    var $content = null;
    var $title   = null;
    var $message = array();

    function admin()
    {
        if (Current_User::allow('rideboard')) {
            Current_User::disallow();
        }

        $this->loadAdminPanel();

        $command = @ $_REQUEST['aop'];
        if (empty($command) || $command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'locations':
            $this->locations();
            break;
        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();

        $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
        $this->panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    function user()
    {
        $this->loadUserPanel();

        $command = @ $_REQUEST['uop'];
        if (empty($command) || $command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'add_ride':

            break;

        case 'view_offers':

            break;

        case 'view_requests':

            break;
        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();

        $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
        $this->panel->setContent($content);
        Layout::addStyle('controlpanel');
        Layout::add($this->panel->display());
    }


    function loadUserPanel()
    {
        $link = PHPWS_Text::linkAddress('rideboard', array('uop'=>'main'));;
        $tabs['add_ride']      = array ('title' => dgettext('rideboard', 'Offer/Request Ride'),
                                        'link'  => $link);
        $tabs['view_offers']   = array ('title' => dgettext('rideboard', 'View ride offers'),
                                        'link'  => $link);
        $tabs['view_requests'] = array ('title' => dgettext('rideboard', 'View ride requests'),
                                        'link'  => $link);

        $this->panel = new PHPWS_Panel('rideboard-user');
        $this->panel->quickSetTabs($tabs);
    }

    function loadAdminPanel()
    {
        $link = PHPWS_Text::linkAddress('rideboard', array('aop'=>'main'));;
        $tabs['locations']      = array ('title' => dgettext('rideboard', 'Locations'),
                                         'link'  => $link);
       
        $this->panel = new PHPWS_Panel('rideboard-admin');
        $this->panel->quickSetTabs($tabs);
    }

    function getMessage()
    {
        return implode('<br />', $this->message);
    }

    function requestRide()
    {

    }

    function locations()
    {

    }

}


?>