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

        case 'settings':
            $this->settings();
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

        case 'post_settings':
            PHPWS_Settings::set('rideboard', 'default_slocation', (int)$_POST['default_slocation']);
            PHPWS_Settings::set('rideboard', 'miles_or_kilometers', (int)$_POST['miles_or_kilometers']);
            PHPWS_Settings::save('rideboard');
            $this->settings();
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
        $command = @ $_REQUEST['uop'];

        switch ($command) {
        case 'user_post':

            if (isset($_POST['post_ride'])) {
                $this->loadRide();
                if ($this->postRide()) {
                    $this->title = dgettext('rideboard', 'Ride posted!');
                    $this->content = PHPWS_Text::moduleLink(dgettext('rideboard', 'Return to Ride Board menu.'));
                } else {
                    $this->userMain();
                }
            } else {
                $this->searchRides();
            }
            break;

        default:
            $this->loadRide();
            $this->userMain();
            break;

        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();

        $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
        Layout::add($content);
    }


    function loadAdminPanel()
    {
        $link = PHPWS_Text::linkAddress('rideboard', array('aop'=>'main'));;
        $tabs['locations']      = array ('title' => dgettext('rideboard', 'Locations'),
                                         'link'  => $link);

        $tabs['settings']      = array ('title' => dgettext('rideboard', 'Settings'),
                                        'link'  => $link);
       
        $this->panel = new PHPWS_Panel('rideboard-admin');
        $this->panel->quickSetTabs($tabs);
    }

    function getMessage()
    {
        return implode('<br />', $this->message);
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
        $tpl['LOCATION_LABEL'] = dgettext('rideboard', 'Locations');

        $pager = new DBPager('rb_location');
        $pager->setModule('rideboard');
        $pager->setTemplate('location.tpl');
        $pager->addPageTags($tpl);
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowFunction(array('Rideboard', 'locationRow'));
        $pager->setDefaultOrder('city_state');

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

    function getLocations()
    {
        $db = new PHPWS_DB('rb_location');
        $db->addColumn('id');
        $db->addColumn('city_state');
        $db->addOrder('city_state');

        $db->setIndexBy('id');
        return $db->select('col');
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

    function settings()
    {
        $form = new PHPWS_Form('settings');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('aop', 'post_settings');

        $locations = $this->getLocations();

        if (PHPWS_Error::logIfError($locations) || empty($locations)) {
            $locations = array(0=> dgettext('rideboard', 'No default'));
        }

        $form->addSelect('default_slocation', $locations);
        $form->setLabel('default_slocation', dgettext('rideboard', 'Default starting location'));
        $form->setMatch('default_slocation', PHPWS_Settings::get('rideboard', 'default_slocation'));
        $form->addSubmit(dgettext('rideboard', 'Save settings'));


        $form->addRadio('miles_or_kilometers', array(0,1));
        $form->setLabel('miles_or_kilometers', array(0=>dgettext('rideboard', 'Miles'),
                                                     1=>dgettext('rideboard', 'Kilometers')));
        $form->setMatch('miles_or_kilometers', PHPWS_Settings::get('rideboard', 'miles_or_kilometers'));
                       
        $tpl = $form->getTemplate();

        $tpl['DISTANCE_LABEL'] = dgettext('rideboard', 'Distance format');
        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'settings.tpl');
        $this->title = dgettext('rideboard', 'Rideboard Settings');
    }

    function loadRide()
    {
        PHPWS_Core::initModClass('rideboard', 'Ride.php');

        if (isset($_REQUEST['rid'])) {
            $this->ride = new RB_Ride($_REQUEST['rid']);
        } else {
            $this->ride = new RB_Ride;
        }
    }

    function userMain()
    {
        $ride = & $this->ride;

        /*
        if ($ride->id) {
            $this->title = dgettext('rideboard', 'Update ride');
        } else {
            $this->title = dgettext('rideboard', 'Post ride');
        }
        */

        $locations = $this->getLocations();
        if (PHPWS_Error::logIfError($locations) || empty($locations)) {
            $locations = array(0 => dgettext('rideboard', '- Location in comments -'));
        } else {
            $locations = array_reverse($locations, true);
            $locations[0] = dgettext('rideboard', '- Locations in comments -');
            $locations = array_reverse($locations, true);
        }


        $form = new PHPWS_Form('ride');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('uop', 'user_post');

        /*
         * Post ride form
         */
        $this->postForm($form, $locations);

        /*
         * Search ride form
         */
        $this->searchForm($form, $locations);

        $tpl = $form->getTemplate();
        $tpl['SEARCH_TIME_LABEL'] = $tpl['DEPART_TIME_LABEL'] = dgettext('rideboard', 'Leaving on');

        $js_vars['form_name'] = 'ride';
        $js_vars['date_name'] = 'depart_time';
        $js_vars['type']      = 'select';
        $tpl['DEPART_JS'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'search_time';
        $tpl['SEARCH_JS'] = javascript('js_calendar', $js_vars);

        $tpl['POST_TITLE'] = dgettext('rideboard', 'Post ride');
        $tpl['SEARCH_TITLE'] = dgettext('rideboard', 'Search for ride');

        
        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'ride_form.tpl');
    }

    function postForm(&$form, $locations)
    {
        $ride = & $this->ride;
        $form->addSelect('ride_type', array(RB_RIDER  => dgettext('rideboard', 'Looking for a ride'),
                                            RB_DRIVER => dgettext('rideboard', 'Offering to drive'),
                                            RB_EITHER => dgettext('rideboard', 'Either')));
        $form->setLabel('ride_type', dgettext('rideboard', 'I am'));
        $form->setMatch('ride_type', $ride->ride_type);

        $form->addSelect('gender_pref', array(RB_MALE   => dgettext('rideboard', 'Male'),
                                              RB_FEMALE => dgettext('rideboard', 'Female'),
                                              RB_EITHER => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('gender_pref', dgettext('rideboard', 'Gender preference'));
        $form->setMatch('gender_pref', $ride->gender_pref);

        $form->addSelect('smoking', array(RB_NONSMOKER  => dgettext('rideboard', 'Non-smokers only'),
                                          RB_SMOKER     => dgettext('rideboard', 'Will ride with smokers'),
                                          RB_EITHER     => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('smoking', dgettext('rideboard', 'Smoking preference'));
        $form->setMatch('smoking', $ride->smoking);

        $form->dateSelect('depart_time', $ride->depart_time, null, 0, 1);

        $form->addText('title', $ride->title);
        $form->setLabel('title', dgettext('rideboard', 'Trip title'));

        $form->addSelect('s_location', $locations);
        $form->setLabel('s_location', dgettext('rideboard', 'Leaving from'));
        $form->setMatch('s_location', $ride->s_location);

        $form->addSelect('d_location', $locations);
        $form->setLabel('d_location', dgettext('rideboard', 'Going to'));
        $form->setMatch('d_location', $ride->d_location);
        $form->addTextArea('comments', $ride->comments);
        $form->setLabel('comments', dgettext('rideboard', 'Comments'));
        $form->addSubmit('post_ride', dgettext('rideboard', 'Post ride'));
    }

    function searchForm(&$form, $locations)
    {
        $form->addSelect('search_s_location', $locations);
        $form->setLabel('search_s_location', dgettext('rideboard', 'Leaving from'));
        $form->setMatch('search_s_location', PHPWS_Settings::get('rideboard', 'default_slocation'));

        $form->addSelect('search_d_location', $locations);
        $form->setLabel('search_d_location', dgettext('rideboard', 'Going to'));
        $form->setMatch('search_d_location', PHPWS_Settings::get('rideboard', 'default_slocation'));

        $form->dateSelect('search_time', $ride->search_time, null, 0, 1);
        $form->addSubmit('search_ride', dgettext('rideboard', 'Search for rides'));
        $form->addText('search_words');
        $form->setLabel('search_words', dgettext('rideboard', 'Search words'));

        $form->addSelect('search_ride_type', array(RB_RIDER  => dgettext('rideboard', 'Passengers'),
                                                   RB_DRIVER => dgettext('rideboard', 'Drivers'),
                                                   RB_EITHER => dgettext('rideboard', 'Anyone')));
        $form->setLabel('search_ride_type', dgettext('rideboard', 'Looking for'));

        $form->addSelect('search_gender_pref', array(RB_MALE   => dgettext('rideboard', 'Male'),
                                                     RB_FEMALE => dgettext('rideboard', 'Female'),
                                                     RB_EITHER => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_gender_pref', dgettext('rideboard', 'Gender preference'));
        
        $form->addSelect('search_smoking', array(RB_NONSMOKER  => dgettext('rideboard', 'Non-smokers only'),
                                                 RB_SMOKER     => dgettext('rideboard', 'Will ride with smokers'),
                                                 RB_EITHER     => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_smoking', dgettext('rideboard', 'Smoking preference'));
    }

    function postRide()
    {
        $errors = array();
        test($_POST);

        if (empty($_POST['title'])) {
            $errors[] = dgettext('rideboard', 'Please give your ride a trip title.');
        } else {
            $this->ride->setTitle($_POST['title']);
        }

        $this->ride->s_location = (int)$_POST['s_location'];
        $this->ride->d_location = (int)$_POST['d_location'];

        if (PHPWS_Form::testDate('depart_time')) {
            $this->ride->depart_time = (int)PHPWS_Form::getPostedDate('depart_time');
        } else {
            $errors[] = dgettext('rideboard', 'Invalid leaving date');
        }

        if (empty($_POST['comments']) && (!$this->ride->s_location || !$this->ride->d_location)) {
            $errors[] = dgettext('rideboard', 'If you haven\'t set your departure and arriving locations, you must add information to your comments.');
        } else {
            $this->ride->setComments($_POST['comments']);
        }

        $this->ride->ride_type   = (int)$_POST['ride_type'];
        $this->ride->gender_pref = (int)$_POST['gender_pref'];
        $this->ride->smoking     = (int)$_POST['smoking'];

        
        

    }
    
}


?>