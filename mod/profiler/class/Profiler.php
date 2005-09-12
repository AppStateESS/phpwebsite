<?php
/**
 * Main action class for Profiler module
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('PFL_STUDENT', 1);
define('PFL_FACULTY', 2);
define('PFL_STAFF',   3);


PHPWS_Core::initModClass('profiler', 'Profile.php');

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
            $profile = & new Profile($_REQUEST['id']);
            if (!empty($profile->_error)) {
                PHPWS_Error::log($profile->_error);
                PHPWS_Core::errorPage(404);
            }
            
            Layout::add($profile->display('large'));
            Profiler::view();
            break;
        }

    }

    function pullRandomProfile($type, $template)
    {
        if (!is_numeric($type)) {
            PHPWS_Core::errorPage('404');
        }
        $db = & new PHPWS_DB('profiles');
        if ($type) {
            $db->addWhere('profile_type', $type);
        }
        $db->addOrder('RAND()');
        $db->addWhere('id', 0, '>');
        $db->setLimit(1);
        $profile = & new Profile;
        $result = $db->loadObject($profile);

        if (empty($result)) {
            return _('Please create a profile in this category.');
        }

        return $profile->display($template);
    }


    function admin()
    {
        $title = $content = $message = NULL;

        if (!Current_User::allow('profiler')) {
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
            $profile = & new Profile($_REQUEST['profile_id']);
            if (PEAR::isError($profile->_error)) {
                PHPWS_Core::errorPage(404);
            }
        }

        switch ($command) {
        case 'new':
            $profile = & new Profile;
            $title = _('Create New Profile');
            $content = Profile_Forms::edit($profile);
            break;

        case 'edit':
            $title = _('Update Profile');
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
            $title = _('Current Profiles');
            $content = Profile_Forms::profileList();
            break;

        case 'post_profile':
            if (!isset($_POST['profile_id']) && PHPWS_Core::isPosted()) {
                $title = _('You recently posted this identical profile.');
                $content = _('Ignoring the repeat.');
                break;
            }

            if (!isset($profile)) {
                $profile = & new Profile;
            }
            $result = $profile->postProfile();
            if (is_array($result)) {
                if ($profile->id) {
                    $title = _('Update Profile');
                } else {
                    $title = _('Create New Profile');
                }
                $message = implode('<br />', $result);
                $content = Profile_Forms::edit($profile);
            } elseif ($result == FALSE) {
                $title = _('Sorry');
                $content = _('An error occurred when saving your profile.');
            } else {
                $title = _('Success');
                $content = _('Profile saved successfully.');
                Layout::metaRoute('index.php?module=profiler');
            }
            break;

        case 'settings':
            $title = _('Settings');
            $content = Profile_Forms::settings();
            break;

        case 'save_settings':
            if (!Current_User::authorized('profiler')) {
                Current_User::disallow();
            }

            $result = Profiler::saveSettings();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Uh oh');
                $content = _('There was a problem saving your settings.');
            } else {
                $title = _('Setting saved');
                $content = PHPWS_Text::secureLink(_('Go back to the Settings page.'), 'profiler');
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

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=profiler';

        $tabs['new']      = array ('title'=>_('New'), 'link'=> $link);
        $tabs['list']     = array ('title'=>_('List'), 'link'=> $link);
        $tabs['settings'] = array ('title'=>_('Settings'), 'link'=> $link);
        $tabs['approval'] = array ('title'=>_('Approval'), 'link'=> $link);

        $panel = & new PHPWS_Panel('profiler');
        $panel->quickSetTabs($tabs);
        $panel->setModule('profiler');

        return $panel;
    }

    function saveSettings()
    {
        if (isset($_POST['profile_sidebar'])) {
            PHPWS_Settings::set('profiler', 'profile_sidebar', 1);
        } else {
            PHPWS_Settings::set('profiler', 'profile_sidebar', 0);
        }
        PHPWS_Settings::set('profiler', 'profile_number',
                            (int)$_POST['profile_number']);

        return PHPWS_Settings::save('profiler');
    }

    /**
     * Pulls up the sidebar profiles
     *
     * I have hardcoded display numbers here for now but if/when categories
     * are added, this will change.
     */
    function view()
    {

        if (!PHPWS_Settings::get('profiler', 'profile_sidebar')) {
            return;
        }

        $limit = PHPWS_Settings::get('profiler', 'profile_number');
        $db = & new PHPWS_DB('profiles');
        $db->addOrder('random');
        $db->setLimit($limit);

        $db->addWhere('profile_type', PFL_STUDENT);
        $student = $db->getObjects('Profile');
        Profiler::_sidebar($student);

        $db->resetWhere();
        $db->addWhere('profile_type', PFL_STAFF);
        $staff = $db->getObjects('Profile');
        Profiler::_sidebar($staff);

        $db->resetWhere();
        $db->addWhere('profile_type', PFL_FACULTY);
        $faculty = $db->getObjects('Profile');
        Profiler::_sidebar($faculty);
    }

    function _sidebar($list)
    {
        if (PEAR::isError($list)) {
            PHPWS_Error::log($list, 'profiler', 'Profiler::_sidebar');
        }

        if (empty($list)) {
            return NULL;
        }

        foreach ($list as $item) {
            $content = $item->display('small');
            Layout::add($content, 'profiler', 'sidebar');
        }
    }
    
}

?>
