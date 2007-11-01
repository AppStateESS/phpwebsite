<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Rideboard {
    var $panel   = null;
    var $title   = null;
    var $message = array();
    var $content = null;

    function user()
    {
        $this->userPanel();

        if (isset($_REQUEST['uop'])) {
            $command = $_REQUEST['uop'];
        } else {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'my_rides':
            $this->myRides();
            break;

        case 'passenger':
            $this->passenger();
            break;

        case 'driver':
            $this->driver();
            break;
        }

        $this->panel->setContent($this->content);
        
        Layout::addStyle('controlpanel');
        Layout::add($this->panel->display());
    }

    function admin()
    {

    }

    function addMessage($message)
    {
        $this->message[]['MESSAGE'] = $message;
    }

    function myRides()
    {
        $this->panel->setCurrentTab('my_rides');
    }

    function passenger()
    {
        $this->panel->setCurrentTab('passenger');
        $passenger_panel = & $this->passengerPanel();

        if (isset($_REQUEST['dop'])) {
            $command = $_REQUEST['dop'];
        } else {
            $command = $passenger_panel->getCurrentTab();
        }

        switch ($command) {
        case 'search_for_passenger':
            $this->loadForms();
            $title = dgettext('rideboard', 'Search for Passengers');
            $content = $this->forms->searchForPassenger();
            break;

        case 'need_driver':
            $this->loadForms();
            $title = dgettext('rideboard', 'Need Driver');
            $content = $this->forms->needDriver();
            break;
        }

        $tpl['TITLE']   = & $title;
        $tpl['CONTENT'] = & $content;

        $passenger_panel->setContent(PHPWS_Template::process($tpl, 'rideboard', 'main.tpl'));
        $this->content = $passenger_panel->display();
    }

    function driver()
    {
        $this->panel->setCurrentTab('driver');
        $driver_panel = & $this->driverPanel();

        if (isset($_REQUEST['dop'])) {
            $command = $_REQUEST['dop'];
        } else {
            $command = $driver_panel->getCurrentTab();
        }

        switch ($command) {
        case 'search_for_driver':
            $tpl['TITLE'] = dgettext('rideboard', 'Search for Driver');
            break;

        case 'need_passengers':
            $tpl['TITLE'] = dgettext('rideboard', 'Need Passengers');
            break;
        }

        $driver_panel->setContent(PHPWS_Template::process($tpl, 'rideboard', 'main.tpl'));
        $this->content = $driver_panel->display();
    }

    function loadForms()
    {
        PHPWS_Core::initModClass('rideboard', 'Forms.php');
        $this->forms = new RB_Forms;
        $this->forms->rideboard = & $this;
    }

    function driverPanel()
    {
        $link = 'index.php?module=rideboard&amp;uop=driver';

        $tabs['search_for_driver'] = array('title' => dgettext('rideboard', 'Search for driver'),
                                           'link'  => $link);

        $tabs['need_passengers'] = array('title' => dgettext('rideboard', 'Need passengers'),
                                         'link'  => $link);

        $panel = new PHPWS_Panel('driver');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    function passengerPanel()
    {
        $link = 'index.php?module=rideboard&amp;uop=passenger';

        $tabs['search_for_passenger'] = array('title' => dgettext('rideboard', 'Search for passengers'),
                                           'link'  => $link);

        $tabs['need_driver'] = array('title' => dgettext('rideboard', 'Need driver'),
                                         'link'  => $link);

        $panel = new PHPWS_Panel('passenger');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    function userPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = PHPWS_Text::linkAddress('rideboard');

        $tabs['my_rides']  = array('title'      => dgettext('rideboard', 'My rides'),
                                   'link'       => $link,
                                   'link_title' => dgettext('rideboard', 'See who has responded to your ride requests'));

        $tabs['passenger'] = array('title'      => dgettext('rideboard', 'Passengers'),
                                   'link'       => $link,
                                   'link_title' => dgettext('rideboard', 'Search for passengers'));

        $tabs['driver']    = array('title'      => dgettext('rideboard', 'Drivers'),
                                   'link'       => $link,
                                   'link_title' => dgettext('rideboard', 'Search for drivers'));
                                  
        $this->panel = new PHPWS_Panel('rb_user_panel');
        $this->panel->quickSetTabs($tabs);
    }

    function getLocations()
    {
        return array(1 => 'Boone, NC',
                     2 => 'Durham, NC',
                     3 => 'Charleston, SC',
                     4 => 'Banner Elk, NC');
    }
    

}

?>