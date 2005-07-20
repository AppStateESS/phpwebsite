<?php
/**
 * Main action class for Profiler module
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('profiler', 'Profile.php');

class Profiler {

    function admin()
    {
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

        case 'list':
            $title = _('Current Profiles');
            $content = Profile_Forms::profileList();
            break;

        case 'post_profile':
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
        $tabs['approval'] = array ('title'=>_('Approval'), 'link'=> $link);

        $panel = & new PHPWS_Panel('profiler');
        $panel->quickSetTabs($tabs);
        $panel->setModule('profiler');

        return $panel;
    }


}

?>
