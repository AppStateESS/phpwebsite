<?php
/**
 * Main action class for Profiler module
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('profiler', 'Profile.php');
PHPWS_Core::initModClass('profiler', 'Division.php');

PHPWS_Core::requireConfig('profiler');

class Profiler {
    function user()
    {
        $content = NULL;

        if (empty($_REQUEST['user_cmd'])) {
            PHPWS_Core::errorPage('404');
        }
        switch ($_REQUEST['user_cmd']) {
        case 'random_profile':
            if (!isset($_REQUEST['type']) || !isset($_REQUEST['template'])) {
                PHPWS_Core::errorPage('404');
            }

            $content = Profiler::pullRandomProfile($_REQUEST['type'], $_REQUEST['template']);
            echo $content;
            exit();
            break;

        case 'view_profile':
            if (!isset($_REQUEST['id'])) {
                PHPWS_Core::errorPage(404);
            }
            $profile = new Profile($_REQUEST['id']);
            if (!empty($profile->_error)) {
                PHPWS_Error::log($profile->_error);
                PHPWS_Core::errorPage(404);
            }

            if (Current_User::allow('profiler')) {
                $vars['command']    = 'edit';
                $vars['profile_id'] = $profile->id;
                $link = PHPWS_Text::secureLink(dgettext('profiler', 'Edit profile'), 'profiler', $vars);
                MiniAdmin::add('profiler', $link);
            }
            
            Layout::add($profile->display('large'));
            break;

        case 'view_div':
            if (!isset($_REQUEST['div_id'])) {
                PHPWS_Core::errorPage('404');
            }

            Profiler::view((int)$_REQUEST['div_id']);
            break;
        }

    }

    function pullRandomProfile($type, $template)
    {
        if (!is_numeric($type)) {
            PHPWS_Core::errorPage('404');
        }
        $db = new PHPWS_DB('profiles');
        if ($type) {
            $db->addWhere('profile_type', $type);
        }
        $db->addOrder('RAND()');
        $db->addWhere('id', 0, '>');
        $db->setLimit(1);
        $profile = new Profile;
        $result = $db->loadObject($profile);

        if (empty($result)) {
            return dgettext('profiler', 'Please create a profile in this category.');
        }

        return $profile->display($template);
    }


    function admin()
    {
        $title = $content = $message = NULL;

        if (!Current_User::authorized('profiler')) {
            Current_User::disallow();
        }

        PHPWS_Core::initModClass('profiler', 'Forms.php');
        $title = $content = $message = NULL;
        $panel = & Profiler::cpanel();
        $panel->enableSecure();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['profile_id'])) {
            $profile = new Profile($_REQUEST['profile_id']);
            if (PEAR::isError($profile->_error)) {
                PHPWS_Core::errorPage(404);
            }
        }
        switch ($command) {
        case 'new':
            $profile = new Profile;
            $title = dgettext('profiler', 'Create New Profile');
            $content = Profile_Forms::edit($profile);
            break;

        case 'edit':
            $title = dgettext('profiler', 'Update Profile');
            $content = Profile_Forms::edit($profile);
            break;

        case 'delete':
            if (!Current_User::authorized('profiler', 'delete_profiles')) {
                Current_User::disallow();
                break;
            } else {
                $profile->delete();
            }
        case 'list':
            $title = dgettext('profiler', 'Current Profiles');
            $content = Profile_Forms::profileList();
            break;

        case 'division':
            $title = dgettext('profiler', 'Divisions');
            $content = Profile_Forms::divisionList();
            break;

        case 'edit_division':
            PHPWS_Core::initModClass('profiler', 'Division.php');
            if (isset($_REQUEST['division_id'])) {
                $division = new Profiler_Division((int)$_REQUEST['division_id']);
            } else {
                $division = new Profiler_Division;
            }

            if ($division->error) {
                PHPWS_Error::log($division->error);
                $content = dgettext('profiler', 'There is a problem with this Profiler division.');
                return;
            }

            $content = Profile_Forms::editDivision($division);
            Layout::nakedDisplay($content);
            break;

        case 'delete_division':
            if (!Current_User::authorized('profiler', 'delete_divisions')) {
                Current_User::disallow();
            }
            if (isset($_REQUEST['division_id'])) {
                $division = new Profiler_Division($_REQUEST['division_id']);
                $division->delete();
            }

            PHPWS_Core::goBack();
            break;

        case 'update_division':
            PHPWS_Core::initModClass('profiler', 'Division.php');            
            if (isset($_REQUEST['division_id'])) {
                $division = new Profiler_Division((int)$_REQUEST['division_id']);
            } else {
                $division = new Profiler_Division;
            }

            if ($division->error) {
                PHPWS_Error::log($division->error);
                $content = dgettext('profiler', 'There is a problem with this Profiler division.');
                return;
            }

            if (!$division->post()) {
                $content = Profile_Forms::editDivision($division, true);
                Layout::nakedDisplay($content);
            } else {
                $result = $division->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                }
                javascript('close_refresh');
            }
            
            break;

        case 'post_profile':
            if (!isset($_POST['profile_id']) && PHPWS_Core::isPosted()) {
                $title = dgettext('profiler', 'You recently posted this identical profile.');
                $content = dgettext('profiler', 'Ignoring the repeat.');
                break;
            }

            if (!isset($profile)) {
                $profile = new Profile;
            }
            $result = $profile->postProfile();

            if (is_array($result)) {
                if ($profile->id) {
                    $title = dgettext('profiler', 'Update Profile');
                } else {
                    $title = dgettext('profiler', 'Create New Profile');
                }
                $message = implode('<br />', $result);
                $content = Profile_Forms::edit($profile);
            } else {
                $result = $profile->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $title = dgettext('profiler', 'Sorry');
                    $content = dgettext('profiler', 'An error occurred when saving your profile.');
                } else {
                    $title = dgettext('profiler', 'Success');
                    if ($profile->approved) {
                        $content = dgettext('profiler', 'Profile saved successfully.');
                    } else {
                        $content = dgettext('profiler', 'Profile saved for approval.');
                    }
                    Layout::metaRoute('index.php?module=profiler&authkey=' . Current_User::getAuthKey());
                }
            }
            break;

        case 'settings':
            $title = dgettext('profiler', 'Settings');
            $content = Profile_Forms::settings();
            break;

        case 'save_settings':
            if (!Current_User::authorized('profiler')) {
                Current_User::disallow();
            }

            $result = Profiler::saveSettings();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = dgettext('profiler', 'Uh oh');
                $content = dgettext('profiler', 'There was a problem saving your settings.');
            } else {
                $title = dgettext('profiler', 'Setting saved');
                $content = PHPWS_Text::secureLink(dgettext('profiler', 'Go back to the Settings page.'), 'profiler');
            }
            break;

        } // End of command switch

        $tpl['CONTENT'] = $content;
        $tpl['MESSAGE'] = $message;
        $tpl['TITLE']   = $title;

        $finalcontent = PHPWS_Template::process($tpl, 'profiler', 'main.tpl');
        $panel->setContent($finalcontent);
        $finalPanel = $panel->display();

        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=profiler';

        $tabs['new']       = array ('title'=> dgettext('profiler', 'New'), 'link'=> $link);
        $tabs['list']      = array ('title'=> dgettext('profiler', 'List'), 'link'=> $link);
        $tabs['division']  = array ('title'=> dgettext('profiler', 'Division'), 'link'=>$link);
        $tabs['settings']  = array ('title'=> dgettext('profiler', 'Settings'), 'link'=> $link);
        //        $tabs['approval']  = array ('title'=> dgettext('profiler', 'Approval'), 'link'=> $link);

        $panel = new PHPWS_Panel('profiler');
        $panel->quickSetTabs($tabs);
        $panel->setModule('profiler');
        return $panel;
    }

    function saveSettings()
    {
        if (isset($_POST['profile_homepage'])) {
            PHPWS_Settings::set('profiler', 'profile_homepage', 1);
        } else {
            PHPWS_Settings::set('profiler', 'profile_homepage', 0);
        }
        PHPWS_Settings::set('profiler', 'profile_number',
                            (int)$_POST['profile_number']);

        return PHPWS_Settings::save('profiler');
    }


    /**
     * Pulls up the homepage profiles
     *
     * I have hardcoded display numbers here for now but if/when categories
     * are added, this will change.
     */
    function view($div_id=0)
    {
        if (!PHPWS_Settings::get('profiler', 'profile_homepage')) {
            return;
        }

        $div = new PHPWS_DB('profiler_division');
        if (!$div_id) {
            $div->addWhere('show_homepage', 1);
        } else {
            $div->addWhere('id', $div_id);
        }

        $div->addOrder('id');
        $division_list = $div->getObjects('Profiler_Division');
        
        if (empty($division_list)) {
            return;
        }

        $limit = PHPWS_Settings::get('profiler', 'profile_number');
        $db = new PHPWS_DB('profiles');

        $tpl = new PHPWS_Template('profiler');
        $tpl->setFile('homepage.tpl');

        foreach ($division_list as $division) {
            if (!$div_id) {
                $db->addOrder('random');
                $db->setLimit($limit);
            }

            $db->addWhere('profile_type', $division->id);

            $profiles = $db->getObjects('Profile');
            $db->reset();
            if (empty($profiles)) {
                continue;
            }

            $tpl->setCurrentBlock('profile-row');
            $col_count = 0;

            foreach ($profiles as $item) {
                $row_tpl = array();
                $row_tpl['PROFILE'] = $item->display('small');

                if ($col_count == PRF_MAXIMUM_COLUMNS) {
                    $row_tpl['SHIFT'] = ' ';
                    $col_count = 0;
                }

                $tpl->setData($row_tpl);
                $tpl->parseCurrentBlock();
                $col_count++;
            }

            $tpl->setCurrentBlock('profile-division');
            $tpl->setData(array('DIV_NAME' => $division->viewLink()));
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        Layout::add($content);
    }
    
}

?>
