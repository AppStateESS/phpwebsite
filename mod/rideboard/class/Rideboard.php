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

        case 'offer_ride':
            $this->offerRide();
            break;

        case 'need_ride':
            $this->needRide();
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

    function offerRide()
    {
        $title = $content = null;

        $this->panel->setCurrentTab('offer_ride');
        $passenger_panel = & $this->offerPanel();

        if (isset($_REQUEST['oop'])) {
            $command = $_REQUEST['oop'];
        } else {
            $command = $passenger_panel->getCurrentTab();
        }

        switch ($command) {
        case 'search_for_passenger':
            $this->loadForms();
            $title = dgettext('rideboard', 'Search for Passengers');
            $content = $this->forms->searchForPassenger();
            break;

        case 'offer_ride':
            $this->loadForms();
            $title = dgettext('rideboard', 'Offer Ride');
            $content = $this->forms->OfferRide();
            break;

        case 'post_request':
            test($_POST);
            break;

        }

        $tpl['TITLE']   = & $title;
        $tpl['CONTENT'] = & $content;

        $passenger_panel->setContent(PHPWS_Template::process($tpl, 'rideboard', 'main.tpl'));
        $this->content = $passenger_panel->display();
    }

    function needRide()
    {
        $this->panel->setCurrentTab('need_ride');
        $driver_panel = & $this->needPanel();

        if (isset($_REQUEST['nop'])) {
            $command = $_REQUEST['nop'];
        } else {
            $command = $driver_panel->getCurrentTab();
        }

        switch ($command) {
        case 'search_for_driver':
            $title = dgettext('rideboard', 'Search for Driver');
            break;
            
        case 'request_ride':
            $this->loadForms();
            $title = dgettext('rideboard', 'Request Ride');
            $content = $this->forms->requestRide();
            break;

        case 'post_request':
            $this->postRequest();
            break;
        }

        $tpl['TITLE']   = & $title;
        $tpl['CONTENT'] = & $content;

        $driver_panel->setContent(PHPWS_Template::process($tpl, 'rideboard', 'main.tpl'));
        $this->content = $driver_panel->display();
    }

    function postRequest()
    {
        $this->testRide();
    }


    function testRide()
    {
        $error = false;

        if ($_POST['s_location'] == $_POST['d_location']) {
            $this->addMessage(dgettext('rideboard', 'Start and destination location may not be the same.'));
            $error = true;
        }

        $departure_time = PHPWS_Form::getPostedDate('departure_time');
        $return_time    = PHPWS_Form::getPostedDate('return_time');

        if ($departure_time >= $return_time) {
            $this->addMessage(dgettext('rideboard', 'Your departure time needs to be before your return time.'));
            $error = true;
        }

        return !$error;
    }

    function loadForms()
    {
        PHPWS_Core::initModClass('rideboard', 'Forms.php');
        $this->forms = new RB_Forms;
        $this->forms->rideboard = & $this;
    }

    function needPanel()
    {
        $link = 'index.php?module=rideboard&amp;uop=need_ride';

        $tabs['search_for_offers'] = array('title' => dgettext('rideboard', 'Search ride offers'),
                                           'link'  => $link);

        $tabs['request_ride'] = array('title' => dgettext('rideboard', 'Request ride'),
                                         'link'  => $link);

        $panel = new PHPWS_Panel('driver');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    function offerPanel()
    {
        $link = 'index.php?module=rideboard&amp;uop=offer_ride';

        $tabs['search_for_passenger'] = array('title' => dgettext('rideboard', 'Search for passengers'),
                                              'link'  => $link);

        $tabs['offer_ride'] = array('title' => dgettext('rideboard', 'Offer ride'),
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

        $tabs['need_ride'] = array('title'      => dgettext('rideboard', 'I need a ride'),
                                   'link'       => $link,
                                   'link_title' => dgettext('rideboard', 'If you need a ride, go here.'));

        $tabs['offer_ride']    = array('title'      => dgettext('rideboard', 'I can offer a ride'),
                                       'link'       => $link,
                                       'link_title' => dgettext('rideboard', 'If you can offer a ride to passengers, go here.'));
                                  
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