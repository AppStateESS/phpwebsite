<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireInc('rideboard', 'defines.php');

class Rideboard {
    var $ride     = null;
    var $panel    = null;
    var $content  = null;
    var $title    = null;
    var $message  = array();

    function admin()
    {
        if (!Current_User::allow('rideboard')) {
            Current_User::disallow();
        }

        $js = false;

        $this->loadAdminPanel();

        $command = @ $_REQUEST['aop'];
        if (empty($command) || $command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'locations':
            $this->locations();
            break;

        case 'edit_location':
            $js = true;
            $this->editLocation();
            break;

        case 'post_location':
            $this->postLocation();
            if (isset($_POST['lid'])) {
                javascript('close_refresh');
                $js = true;
            } else {
                PHPWS_Core::goBack();
            }
            break;
        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();


        if ($js) {
            $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
            Layout::nakedDisplay($content);
        } else {
            $content = PHPWS_Template::process($tpl, 'rideboard', 'panel_main.tpl');
            $this->panel->setContent($content);
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }
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

    function locationForm($id=0)
    {
        $form = new PHPWS_Form('location');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('aop', 'post_location');
        $form->addText('city_state');
        if ($id) {
            $db = new PHPWS_DB('rb_location');
            $db->addWhere('id', (int)$id);
            $db->addColumn('city_state');
            $location = $db->select('one');
            if (!PHPWS_Error::logIfError($location) && !empty($location)) {
                $form->addHidden('lid', $id);                
                $form->setValue('city_state', $location);
            }
            // edit uses the javascript popup
            $form->setLabel('city_state', dgettext('rideboard', 'Edit location'));
            $form->addTplTag('CANCEL', javascript('close_window'));
        } else {
            $form->setLabel('city_state', dgettext('rideboard', 'New location'));
        }
        $form->addSubmit(dgettext('rideboard', 'Go'));
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'rideboard', 'edit_location.tpl');
    }

    function locations()
    {
        $this->title = dgettext('rideboard', 'Edit locations');
        PHPWS_Core::initCoreClass('DBPager.php');
        $tpl['ADD_LOCATION'] = $this->locationForm();

        $pager = new DBPager('rb_location');
        $pager->setModule('rideboard');
        $pager->setTemplate('location.tpl');
        $pager->addPageTags($tpl);
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowFunction(array('Rideboard', 'locationRow'));

        $this->content = $pager->get();
    }

    function editLocation()
    {
        $this->title = dgettext('rideboard', 'Edit location');
        $this->content = $this->locationForm($_GET['lid']);
    }

    function postLocation()
    {
        if(empty($_POST['city_state'])) {
            return;
        }

        $db = new PHPWS_DB('rb_location');
        $db->addValue('city_state', strip_tags($_POST['city_state']));
        if (isset($_POST['lid'])) {
            $db->addWhere('id', (int)$_POST['lid']);
            PHPWS_Error::logIfError($db->update());
        } else {
            PHPWS_Error::logIfError($db->insert());
        }
    }

    function locationRow($value)
    {
        $js['address'] = PHPWS_Text::linkAddress('rideboard', array('aop'=>'edit_location',
                                                                    'lid'=>$value['id']),
                                                 true);
        $js['label'] = dgettext('rideboard', 'Edit');
        $js['link_title'] = sprintf(dgettext('rideboard', 'Edit the location %s'), $value['city_state']);
        $js['height'] = 180;
        $links[] = javascript('open_window', $js);
        $tpl['LINKS'] = implode(' | ', $links);
        return $tpl;
    }

}


?>