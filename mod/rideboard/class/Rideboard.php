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
            if (!Current_User::authorized('rideboard')) {
                Current_User::disallow(null, false);
            }

            $this->postLocation();
            if (isset($_POST['lid'])) {
                javascript('close_refresh');
                $js = true;
            } else {
                PHPWS_Core::goBack();
            }
            break;

        case 'purge_rides':
            if (!Current_User::authorized('rideboard')) {
                Current_User::disallow(null, false);
            }
            $this->purgeRides();
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('rideboard', array('aop'=>'settings')));
            break;
           

        case 'post_settings':
            if (!Current_User::authorized('rideboard')) {
                Current_User::disallow(null, false);
            }
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

    function searchSession()
    {
        $_SESSION['rb_search'] = array();

        if (!empty($_POST['search_words'])) {
            $search = preg_replace('/[^\w\s]/', '', $_POST['search_words']);
            $search = preg_replace('/\s{2,}/', ' ', $search);
            $_SESSION['rb_search']['search'] = preg_replace('/\s/', '|', $search);
        }
        
        $_SESSION['rb_search']['search_time']        = PHPWS_Form::getPostedDate('search_time');
        $_SESSION['rb_search']['search_ride_type']   = (int)$_POST['search_ride_type'];
        $_SESSION['rb_search']['search_gender_pref'] = (int)$_POST['search_gender_pref'];
        $_SESSION['rb_search']['search_smoking']     = (int)$_POST['search_smoking'];
        $_SESSION['rb_search']['s_location']         = (int)$_POST['s_location'];
        $_SESSION['rb_search']['d_location']         = (int)$_POST['d_location'];
    }

    function user()
    {
        Current_User::requireLogin();

        $command = @ $_REQUEST['uop'];

        $js = false;

        switch ($command) {
        case 'view_ride':
            $js = true;
            $this->viewRide();
            break;

        case 'search_rides':
            $this->searchRides();
            break;
        case 'user_post':
            if (isset($_POST['post_ride'])) {
                $this->loadRide();
                if ($this->postLimit()) {
                    $this->title = dgettext('rideboard', 'Sorry');
                    $this->content = sprintf(dgettext('rideboard', 'You are limited to %s ride posts per account.'),
                                             PHPWS_Settings::get('rideboard', 'post_limit'));
                } elseif ($this->postRide()) {
                    if (PHPWS_Error::logIfError($this->ride->save())) {
                        $this->title = dgettext('rideboard', 'Sorry');
                        $this->content = dgettext('rideboard', 'An error occurred when trying to save your ride. Please try again later.');
                        $this->content .= '<br />' . PHPWS_Text::moduleLink(dgettext('rideboard', 'Return to Ride Board menu.'), 'rideboard');
                    } else {
                        $this->title = dgettext('rideboard', 'Ride posted!');
                        $this->content = PHPWS_Text::moduleLink(dgettext('rideboard', 'Return to Ride Board menu.'), 'rideboard');
                    }
                } else {
                    $this->userMain();
                }
            } else {
                $this->searchSession();
                PHPWS_Core::reroute(PHPWS_Text::linkAddress('rideboard', array('uop'=>'search_rides')));
            }
            break;

        case 'view_my_rides':
            $this->viewMyRides();
            break;

        case 'delete_ride':
            $this->loadRide();
            if ($this->ride->id) {
                if ( Current_User::authorized('rideboard') || 
                     (Current_User::verifyAuthKey() && Current_User::getId() == $this->ride->user_id) ) {
                    $this->ride->delete();
                }
            }
            PHPWS_Core::goBack();
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
        if ($js) {
            Layout::nakedDisplay($content);
        } else {
            Layout::add($content);
        }
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

        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('depart_time', mktime(), '<');
        $db->addColumn('id');
        $old_rides = $db->count();

        if ($old_rides) {
            $tpl['PURGE'] = PHPWS_Text::secureLink(sprintf(dngettext('rideboard', 'You have %s ride that has expired. Click here to purge it.',
                                                                     'You have %s rides that have expired. Click here to purge them.', $old_rides),
                                                           $old_rides),
                                                   'rideboard',
                                                   array('aop'=>'purge_rides')
                                                   );
        } else {
            $tpl['PURGE'] = dgettext('rideboard', 'No rides need purging.');
        }

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
        $tpl['SEARCH_TIME_LABEL'] = dgettext('rideboard', 'Leaving around');
        $tpl['DEPART_TIME_LABEL'] = dgettext('rideboard', 'Leaving on');

        $js_vars['form_name'] = 'ride';
        $js_vars['date_name'] = 'depart_time';
        $js_vars['type']      = 'select';
        $tpl['DEPART_JS'] = javascript('js_calendar', $js_vars);

        $js_vars['date_name'] = 'search_time';
        $tpl['SEARCH_JS'] = javascript('js_calendar', $js_vars);

        $tpl['POST_TITLE'] = dgettext('rideboard', 'Post ride');
        $tpl['SEARCH_TITLE'] = dgettext('rideboard', 'Search for ride');

        $links[] = PHPWS_Text::moduleLink(dgettext('rideboard', 'View my rides'), 'rideboard',
                                          array('uop' => 'view_my_rides'));

        $tpl['LINKS'] = implode(' | ', $links);
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
        $form->setLabel('d_location', dgettext('rideboard', 'Destination'));
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
        $form->setLabel('search_d_location', dgettext('rideboard', 'Destination'));

        $form->dateSelect('search_time', null, null, 0, 1);
        $form->addSubmit('search_ride', dgettext('rideboard', 'Search for rides'));
        $form->addText('search_words');
        $form->setLabel('search_words', dgettext('rideboard', 'Search words'));

        $form->addSelect('search_ride_type', array(RB_RIDER  => dgettext('rideboard', 'Rider'),
                                                   RB_DRIVER => dgettext('rideboard', 'Drivers'),
                                                   RB_EITHER => dgettext('rideboard', 'Anyone')));
        $form->setMatch('search_ride_type', RB_EITHER);
        $form->setLabel('search_ride_type', dgettext('rideboard', 'Looking for'));

        $form->addSelect('search_gender_pref', array(RB_MALE   => dgettext('rideboard', 'Male'),
                                                     RB_FEMALE => dgettext('rideboard', 'Female'),
                                                     RB_EITHER => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_gender_pref', dgettext('rideboard', 'Gender preference'));
        $form->setMatch('search_gender_pref', RB_EITHER);
        
        $form->addSelect('search_smoking', array(RB_NONSMOKER  => dgettext('rideboard', 'Non-smokers only'),
                                                 RB_SMOKER     => dgettext('rideboard', 'Smokers welcome'),
                                                 RB_EITHER     => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_smoking', dgettext('rideboard', 'Smoking preference'));
        $form->setMatch('search_smoking', RB_EITHER);
    }

    function postRide()
    {
        if (PHPWS_Core::isPosted()) {
            return false;
        }

        $errors = array();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('rideboard', 'Please give your ride a trip title.');
        } else {
            $this->ride->setTitle($_POST['title']);
        }

        $this->ride->s_location = (int)$_POST['s_location'];
        $this->ride->d_location = (int)$_POST['d_location'];

        if ($this->ride->s_location && 
            $this->ride->s_location == $this->ride->d_location) {
            $errors[] = dgettext('rideboard', 'Your leaving and arriving locations must be different.');
        }

        if (PHPWS_Form::testDate('depart_time')) {
            $this->ride->depart_time = (int)PHPWS_Form::getPostedDate('depart_time');
            if ($this->ride->depart_time < mktime()) {
                $errors[] = dgettext('rideboard', 'Your leaving date must be in the future.');
            }
        } else {
            $errors[] = dgettext('rideboard', 'Invalid leaving date');
        }

        if (empty($_POST['comments']) && (!$this->ride->s_location || !$this->ride->d_location)) {
            $errors[] = dgettext('rideboard', 'If you haven\'t set your leaving and arriving locations, you must add information to your comments.');
        } else {
            $this->ride->setComments($_POST['comments']);
        }

        $this->ride->ride_type   = (int)$_POST['ride_type'];
        $this->ride->gender_pref = (int)$_POST['gender_pref'];
        $this->ride->smoking     = (int)$_POST['smoking'];

        if (!empty($errors)) {
            $this->message = $errors;
            return false;
        } else {
            return true;
        }
    }

    function postLimit()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('user_id', Current_User::getId());
        $result = $db->count();
        if (PHPWS_Error::logIfError($result)) {
            return true;
        }

        return ($result >= PHPWS_Settings::get('rideboard', 'post_limit'));
    }

    function getRidesDB()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addTable('rb_location', 't1');
        $db->addTable('rb_location', 't2');
        $db->loadClass('rideboard', 'Ride.php');
        $db->addOrder('depart_time desc');
        $db->addColumn('*');
        $db->addJoin('left', 'rb_ride', 't1', 's_location', 'id');
        $db->addJoin('left', 'rb_ride', 't2', 'd_location', 'id');

        $db->addColumn('t1.city_state', null, 'start_location');
        $db->addColumn('t2.city_state', null, 'dest_location');
        
        return $db;
    }

    function viewMyRides()
    {
        $this->title = dgettext('rideboard', 'View my Rides');
        $db = $this->getRidesDB();
        $db->addWhere('user_id', Current_User::getId());

        $result = $db->getObjects('RB_Ride');

        if (PHPWS_Error::logIfError($result)) {
            $this->content = dgettext('rideboard', 'An error occurred when pulling your rides.');
            return;
        }

        if (empty($result)) {
            $this->content = dgettext('rideboard', 'You do not have any rides.');
            return;
        }

        foreach ($result as $ride) {
            $tpl['rides'][] = $ride->tags();
        }

        $tpl['TITLE_LABEL']     = dgettext('rideboard', 'Trip title');
        $tpl['RIDE_TYPE_LABEL'] = dgettext('rideboard', 'Driver or Rider');
        $tpl['PREF_LABEL']      = dgettext('rideboard', 'Gender - Smoking Preference');
        $tpl['START_LABEL']     = dgettext('rideboard', 'Leaving from');
        $tpl['DEST_LABEL']      = dgettext('rideboard', 'Destination');

        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'my_rides.tpl');
    }

    function searchRides()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('rideboard', 'Ride.php');


        if (!isset($_SESSION['rb_search'])) {
            $this->title = dgettext('rideboard', 'Sorry');
            $this->content = dgettext('rideboard', 'Your session timed out.');
            $this->content .= '<br />' . PHPWS_Text::moduleLink(dgettext('rideboard', 'Back to search'), 'rideboard');
            return;
        }
        
        $tpl['LINK'] = PHPWS_Text::moduleLink(dgettext('rideboard', 'Back to search'), 'rideboard');
        $tpl['TITLE_LABEL']     = dgettext('rideboard', 'Trip title');
        $tpl['RIDE_TYPE_LABEL'] = dgettext('rideboard', 'Driver or Rider');
        $tpl['RIDE_TYPE_ABBR']  = dgettext('rideboard', 'D/R');
        $tpl['GEN_PREF_LABEL']  = dgettext('rideboard', 'Gender');
        $tpl['SMOKE_LABEL']     = dgettext('rideboard', 'Smoking');
        $tpl['START_LABEL']     = dgettext('rideboard', 'Leaving from/on');
        $tpl['DEST_LABEL']      = dgettext('rideboard', 'Destination');

        $pager = new DBPager('rb_ride', 'RB_Ride');
        $pager->setModule('rideboard');
        $pager->setTemplate('search_rides.tpl');
        $pager->addRowTags('tags', false);
        $pager->addPageTags($tpl);
        $pager->setEmptyMessage(dgettext('rideboard', 'No rides found fitting your criteria.'));

        $pager->joinResult('s_location', 'rb_location', 'id', 'city_state', 'start_location');
        $pager->joinResult('d_location', 'rb_location', 'id', 'city_state', 'dest_location');

        extract($_SESSION['rb_search']);

        if (!empty($search)) {
            $pager->db->addWhere('title', $search, 'regexp', 'or', 1);
            $pager->db->addWhere('comments', $search, 'regexp', 'or', 1);
        }

        if ($s_location) {
            $pager->db->addWhere('s_location', $s_location);
        }

        if ($d_location) {
            $pager->db->addWhere('s_location', $d_location);
        }

        $search_before = $search_time - (86400 * 7);
        $search_after  = $search_time + (86400 * 7);

        if ($search_before < mktime()) {
            $search_before = mktime();
        }

        $pager->db->addWhere('depart_time', $search_before, '>', null, 'time');
        $pager->db->addWhere('depart_time', $search_after, '<', null, 'time');

        if ($search_ride_type != RB_EITHER) {
            $pager->db->addWhere('ride_type', $search_ride_type);
        }

        if ($search_smoking != RB_EITHER) {
            $pager->db->addWhere('smoking', $search_smoking);
        }

        if ($search_gender_pref != RB_EITHER) {
            $pager->db->addWhere('gender_pref', $search_gender_pref);
        }


        //        $pager->db->setTestMode();
        $this->title = dgettext('rideboard', 'Search rides');
        $this->content = $pager->get();
    }

    function viewRide()
    {
        $this->loadRide();
        if (!$this->ride->id) {
            $this->title = dgettext('rideboard', 'Sorry');
            $this->content = dgettext('rideboard', 'This ride could not be found.');
            return;
        }
        $this->title = $this->ride->title;
        $this->content = $this->ride->view();
    }
  
    function purgeRides()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('depart_time', mktime(), '<');
        return !PHPWS_Error::logIfError($db->delete());
    }
  
}


?>