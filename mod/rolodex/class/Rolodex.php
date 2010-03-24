<?php
/**
 * rolodex - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */


PHPWS_Core::requireInc('rolodex', 'errordefines.php');
PHPWS_Core::requireConfig('rolodex');
PHPWS_Core::requireConfig('comments');

class Rolodex {
    public $forms    = null;
    public $panel    = null;
    public $title    = null;
    public $message  = null;
    public $content  = null;
    public $member   = null;
    public $location = null;
    public $feature  = null;
    public $category = null;


    public function adminMenu()
    {
        if (!Current_User::allow('rolodex')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;

        $this->loadMessage();

        /* This switch determines if 'settings' panel needs creating */
        switch($_REQUEST['aop']) {
            case 'post_settings':
            case 'reset_expired':
            case 'delete_expired':
            case 'search_index_all':
            case 'search_remove_all':
            case 'all_comments_yes':
            case 'all_comments_no':
            case 'all_anon_yes':
            case 'all_anon_no':
            case 'new_location':
            case 'edit_location':
            case 'post_location':
            case 'delete_location':
            case 'new_feature':
            case 'edit_feature':
            case 'post_feature':
            case 'delete_feature':
                PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                $settingsPanel = Rolodex_Forms::settingsPanel();
                $settingsPanel->enableSecure();
                break;
            case 'menu':
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'utilities' || $_GET['tab'] == 'locations' || $_GET['tab'] == 'features') {
                        PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                        $settingsPanel = Rolodex_Forms::settingsPanel();
                        $settingsPanel->enableSecure();
                    }
                }

        }

        /* This switch dumps the content in */
        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'post_settings':
                $settingsPanel->setCurrentTab('settings');
                if (!Current_User::authorized('rolodex', 'settings')) {
                    Current_User::disallow();
                }
                $oldsearch = PHPWS_Settings::get('rolodex', 'privacy_use_search');
                if ($this->postSettings()) {
                    $msg = dgettext('rolodex', 'Rolodex settings saved.') . '<br />';
                    if ($oldsearch != PHPWS_Settings::get('rolodex', 'privacy_use_search')) {
                        if (PHPWS_Settings::get('rolodex', 'privacy_use_search')) {
                            if ($this->search_index_all())
                            $msg .= dgettext('rolodex', 'All current member records have been indexed with the search module.');
                        } else {
                            if ($this->search_remove_all())
                            $msg .= dgettext('rolodex', 'All current member records have been removed from the search module.');
                        }
                    }
                    $this->forwardMessage($msg);
                    PHPWS_Core::reroute('index.php?module=rolodex&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

            case 'edit_member':
                $this->loadForm('edit_member');
                break;

            case 'post_member':
                if (!Current_User::authorized('rolodex')) {
                    Current_User::disallow();
                }
                if ($this->postMember()) {
                    if (PHPWS_Error::logIfError($this->member->saveMember())) {
                        $this->forwardMessage(dgettext('rolodex', 'Error occurred when saving member profile.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&aop=edit_member&user_id=' . $this->member->user_id);
                    } else {
                        $this->forwardMessage(dgettext('rolodex', 'Member profile saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&id=' . $this->member->user_id);
                    }
                } else {
                    $this->loadForm('edit_member');
                }
                break;

            case 'activate_member':
                if (Current_User::isRestricted('rolodex')) {
                    Current_User::disallow();
                }
                $this->loadMember();
                $this->member->active = 1;
                $this->member->save();
                $this->member->saveKey();
                $this->message = dgettext('rolodex', 'Rolodex member activated.');
                $this->loadForm('list');
                break;

            case 'deactivate_member':
                if (Current_User::isRestricted('rolodex')) {
                    Current_User::disallow();
                }
                $this->loadMember();
                $this->member->active = 0;
                $this->member->save();
                $this->member->saveKey();
                $this->message = dgettext('rolodex', 'Rolodex member deactivated.');
                $this->loadForm('list');
                break;

            case 'reset_expired':
                $settingsPanel->setCurrentTab('utilities');
                $interval = PHPWS_Settings::get('rolodex', 'expiry_interval');
                $this->resetExpired($interval);
                $this->message = sprintf(dgettext('rolodex', 'All expiry dates set to %s days from now.'), $interval);
                $this->loadForm('utilities');
                break;

            case 'delete_expired':
                $settingsPanel->setCurrentTab('utilities');
                $num = $this->deleteExpired();
                if ($num > 0) {
                    $this->message = sprintf(dgettext('rolodex', '%s expired member(s) were deleted.'), $num);
                } else {
                    $this->message = dgettext('rolodex', 'There were no expired records to delete.');
                }
                $this->loadForm('utilities');
                break;

            case 'search_index_all':
                $settingsPanel->setCurrentTab('utilities');
                if (PHPWS_Settings::get('rolodex', 'privacy_use_search')) {
                    $this->search_index_all();
                    $this->message = dgettext('rolodex', 'All current member records have been indexed with the search module.');
                } else {
                    $this->message = dgettext('rolodex', 'Search indexing is disabled in Rolodex settings. You must enable it there before you may perform this action.');
                }
                $this->loadForm('utilities');
                break;

            case 'search_remove_all':
                $settingsPanel->setCurrentTab('utilities');
                if (PHPWS_Settings::get('rolodex', 'privacy_use_search')) {
                    $this->message = dgettext('rolodex', 'Search indexing is enabled in Rolodex settings. You must disable it there before you may perform this action.');
                } else {
                    $this->search_remove_all();
                    $this->message = dgettext('rolodex', 'All current member records have been removed from the search module.');
                }
                $this->loadForm('utilities');
                break;

            case 'all_comments_yes':
                $settingsPanel->setCurrentTab('utilities');
                $this->setAllComments(1);
                $this->message = dgettext('rolodex', 'Allow comments has been set to yes on all members.');
                $this->loadForm('utilities');
                break;

            case 'all_comments_no':
                $settingsPanel->setCurrentTab('utilities');
                $this->setAllComments(0);
                $this->message = dgettext('rolodex', 'Allow comments has been set to no on all members.');
                $this->loadForm('utilities');
                break;

            case 'all_anon_yes':
                $settingsPanel->setCurrentTab('utilities');
                $this->setAllComments_annon(1);
                $this->message = dgettext('rolodex', 'Allow anonymous comments has been set to yes on all members.');
                $this->loadForm('utilities');
                break;

            case 'all_anon_no':
                $settingsPanel->setCurrentTab('utilities');
                $this->setAllComments_annon(0);
                $this->message = dgettext('rolodex', 'Allow anonymous comments has been set to no on all members.');
                $this->loadForm('utilities');
                break;

            case 'delete_member':
                $this->loadMember();
                $this->member->deleteMember();
                $this->message = dgettext('rolodex', 'Rolodex member deleted.');
                $this->loadForm('list');
                break;

            case 'new_location':
            case 'edit_location':
                $settingsPanel->setCurrentTab('locations');
                $this->loadForm('edit_location');
                break;

            case 'post_location':
                $settingsPanel->setCurrentTab('locations');
                if ($this->postLocation()) {
                    if (PHPWS_Error::logIfError($this->location->save())) {
                        $this->forwardMessage(dgettext('rolodex', 'Error occurred when saving location.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&aop=edit_location&location=' . $this->location->id);
                    } else {
                        $this->forwardMessage(dgettext('rolodex', 'Location saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&aop=menu&tab=locations');
                    }
                } else {
                    $this->loadForm('edit_location');
                }
                break;

            case 'delete_location':
                $settingsPanel->setCurrentTab('locations');
                $this->loadLocation();
                $this->location->delete();
                $this->message = dgettext('rolodex', 'Location deleted.');
                $this->loadForm('locations');
                break;

            case 'new_feature':
            case 'edit_feature':
                $settingsPanel->setCurrentTab('features');
                $this->loadForm('edit_feature');
                break;

            case 'post_feature':
                $settingsPanel->setCurrentTab('features');
                if ($this->postFeature()) {
                    if (PHPWS_Error::logIfError($this->feature->save())) {
                        $this->forwardMessage(dgettext('rolodex', 'Error occurred when saving feature.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&aop=edit_feature&feature=' . $this->feature->id);
                    } else {
                        $this->forwardMessage(dgettext('rolodex', 'Feature saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&aop=menu&tab=features');
                    }
                } else {
                    $this->loadForm('edit_feature');
                }
                break;

            case 'delete_feature':
                $settingsPanel->setCurrentTab('features');
                $this->loadFeature();
                $this->feature->delete();
                $this->message = dgettext('rolodex', 'Feature deleted.');
                $this->loadForm('features');
                break;

        }

        /* This switch creates the 'settings' panel when needed */
        switch($_REQUEST['aop']) {
            case 'post_settings':
            case 'reset_expired':
            case 'delete_expired':
            case 'search_index_all':
            case 'search_remove_all':
            case 'all_comments_yes':
            case 'all_comments_no':
            case 'all_anon_yes':
            case 'all_anon_no':
            case 'new_location':
            case 'edit_location':
            case 'post_location':
            case 'delete_location':
            case 'new_feature':
            case 'edit_feature':
            case 'post_feature':
            case 'delete_feature':
                $settingsPanel->setContent($this->content);
                $this->content = $settingsPanel->display();
            case 'menu':
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'utilities' || $_GET['tab'] == 'locations' || $_GET['tab'] == 'features') {
                        $settingsPanel->setContent($this->content);
                        $this->content = $settingsPanel->display();
                    }
                }
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'rolodex', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'rolodex', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    public function sendMessage()
    {
        PHPWS_Core::reroute('index.php?module=rolodex&amp;uop=message');
    }

    public function forwardMessage($message, $title=null)
    {
        $_SESSION['RDX_Message']['message'] = $message;
        if ($title) {
            $_SESSION['RDX_Message']['title'] = $title;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['RDX_Message'])) {
            $this->message = $_SESSION['RDX_Message']['message'];
            if (isset($_SESSION['RDX_Message']['title'])) {
                $this->title = $_SESSION['RDX_Message']['title'];
            }
            PHPWS_Core::killSession('RDX_Message');
        }
    }


    public function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }

        $this->loadMessage();

        switch ($action) {

            case 'message':
                $this->loadMessage();
                if (empty($this->message)) {
                    PHPWS_Core::home();
                }
                $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                break;

            case 'list':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listMembers(1);
                }
                break;

            case 'categories':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    //                    $this->forms->categories();
                    $this->forms->listCategories();
                }
                break;

            case 'locations':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listLocations();
                }
                break;

            case 'features':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listFeatures();
                }
                break;

            case 'advanced':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->advSearchForm();
                }
                break;

            case 'adv_search':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listMembers(1);
                }
                break;

            case 'export':
                PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
                if (Rolodex_Member::isDataVisible('privacy_export')) {
                    $this->exportCSV();
                } else {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, your access does not allow CSV exporting.');
                }
                break;

            case 'view_member':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this member.');
                } else {
                    $this->loadMember();
                    if ($this->member->isMemberVisible()) {
                        if (PHPWS_Settings::get('rolodex', 'enable_expiry')) {
                            if ($this->member->date_expires <= time()) {
                                $this->forwardMessage(dgettext('rolodex', 'Sorry, this membership has expired.'));
                                if (!Current_User::isUnrestricted('rolodex'))
                                PHPWS_Core::reroute('index.php?module=rolodex&uop=list');
                            }
                        }
                        $this->title = $this->member->getDisplay_name(true);
                        $this->content = $this->member->view();
                    } else {
                        $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                        $this->content = dgettext('rolodex', 'Sorry, this user has restricted privacy settings.');
                    }
                }
                break;

            case 'submit_member':
                $this->loadForm('edit_my_member');
                break;

            case 'add_member':
                $this->loadForm('edit_my_member');
                break;

            case 'edit_member':
                $this->loadForm('edit_my_member');
                break;

            case 'message_member':
                $this->loadForm('message_member');
                $this->title = sprintf(dgettext('rolodex', 'Send a message to %s'), $this->member->getDisplay_name(true));
                break;

            case 'send_message':
                if ($this->checkMessage()) {
                    if (!PHPWS_Error::logIfError($this->sendMail())) {
                        $this->forwardMessage(dgettext('rolodex', 'Message sent succesfully.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&id=' . $this->member->user_id);
                    } else {
                        $this->forwardMessage(dgettext('rolodex', 'There was a problem sending the message.'));
                        PHPWS_Core::reroute('index.php?module=rolodex&id=' . $this->member->user_id);
                    }
                } else {
                    $this->loadForm('message_member');
                    $this->title = sprintf(dgettext('rolodex', 'Send a message to %s'), $this->member->getDisplay_name(true));
                }
                break;

            case 'post_member':
                //            print(Current_User::getId() . ':' . $_REQUEST['user_id']); exit;
                //            print($_SESSION['User']->id . ':' . $_REQUEST['user_id']); exit;
                //                if (Current_User::getId() !== $_REQUEST['user_id']) {
                if ($_SESSION['User']->id != $_REQUEST['user_id']) {
                    Current_User::disallow();
                }
                if ($this->postMember()) {
                    if (PHPWS_Error::logIfError($this->member->saveMember())) {
                        if (PHPWS_Settings::get('rolodex', 'req_approval')) {
                            $this->forwardMessage(dgettext('rolodex', 'Error occurred when submitting member profile.'));
                        } else {
                            $this->forwardMessage(dgettext('rolodex', 'Error occurred when saving member profile.'));
                        }
                        PHPWS_Core::reroute('index.php?module=rolodex&uop=edit_member&user_id=' . $this->member->user_id);
                    } else {
                        if (PHPWS_Settings::get('rolodex', 'req_approval') && $this->member->isNew()) {
                            $this->forwardMessage(dgettext('rolodex', 'Member profile submitted successfully. An admin will review.'));
                            PHPWS_Core::reroute('index.php?module=rolodex&uop=list');
                        } else {
                            $this->forwardMessage(dgettext('rolodex', 'Member profile saved successfully.'));
                            PHPWS_Core::reroute('index.php?module=rolodex&id=' . $this->member->user_id);
                        }
                    }
                } else {
                    $this->loadForm('edit_member');
                }
                break;

            case 'view_location':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    if (isset($_REQUEST['location_id'])) {
                        $id = $_REQUEST['location_id'];
                    } elseif (isset($_REQUEST['location'])) {
                        $id = $_REQUEST['location'];
                    } elseif (isset($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                    }
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listMembers(1, false, $id, null, null);
                }
                break;

            case 'view_feature':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    if (isset($_REQUEST['feature_id'])) {
                        $id = $_REQUEST['feature_id'];
                    } elseif (isset($_REQUEST['feature'])) {
                        $id = $_REQUEST['feature'];
                    } elseif (isset($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                    }
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listMembers(1, false, null, $id, null);
                }
                break;

            case 'view_category':
                if (!PHPWS_Settings::get('rolodex', 'allow_anon') && !Current_User::getId()) {
                    $this->title = PHPWS_Settings::get('rolodex', 'module_title');
                    $this->content = dgettext('rolodex', 'Sorry, anonymous member viewing is not allowed. You will need to login to view this directory.');
                } else {
                    if (isset($_REQUEST['category_id'])) {
                        $id = $_REQUEST['category_id'];
                    } elseif (isset($_REQUEST['category'])) {
                        $id = $_REQUEST['category'];
                    } elseif (isset($_REQUEST['id'])) {
                        $id = $_REQUEST['id'];
                    }
                    PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
                    $this->forms = new Rolodex_Forms;
                    $this->forms->rolodex = & $this;
                    $this->forms->listMembers(1, false, null, null, $id);
                }
                break;


        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'rolodex', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'rolodex', 'main_user.tpl'));
        }

    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Forms.php');
        $this->forms = new Rolodex_Forms;
        $this->forms->rolodex = & $this;
        $this->forms->get($type);
    }


    public function loadMember($user_id=0)
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');

        if ($user_id) {
            $this->member = new Rolodex_Member($user_id);
        } elseif (isset($_REQUEST['user_id'])) {
            $this->member = new Rolodex_Member($_REQUEST['user_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->member = new Rolodex_Member($_REQUEST['id']);
        } else {
            $this->member = new Rolodex_Member;
        }
    }


    public function loadLocation($id=0)
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Location.php');

        if ($id) {
            $this->location = new Rolodex_Location($id);
        } elseif (isset($_REQUEST['location_id'])) {
            $this->location = new Rolodex_Location($_REQUEST['location_id']);
        } elseif (isset($_REQUEST['location'])) {
            $this->location = new Rolodex_Location($_REQUEST['location']);
        } elseif (isset($_REQUEST['id'])) {
            $this->location = new Rolodex_Location($_REQUEST['id']);
        } else {
            $this->location = new Rolodex_Location;
        }
    }


    public function loadFeature($id=0)
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Feature.php');

        if ($id) {
            $this->feature = new Rolodex_Feature($id);
        } elseif (isset($_REQUEST['feature_id'])) {
            $this->feature = new Rolodex_Feature($_REQUEST['feature_id']);
        } elseif (isset($_REQUEST['feature'])) {
            $this->feature = new Rolodex_Feature($_REQUEST['feature']);
        } elseif (isset($_REQUEST['id'])) {
            $this->feature = new Rolodex_Feature($_REQUEST['id']);
        } else {
            $this->feature = new Rolodex_Feature;
        }
    }


    public function loadCategory($id=0)
    {
        PHPWS_Core::initModClass('categories', 'Category.php');

        if ($id) {
            $this->category = new Category($id);
        } elseif (isset($_REQUEST['category_id'])) {
            $this->category = new Category($_REQUEST['category_id']);
        } elseif (isset($_REQUEST['category'])) {
            $this->category = new Category($_REQUEST['category']);
        } else {
            $this->category = new Category($_REQUEST['id']);
        }

    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('rolodex-panel');
        $link = 'index.php?module=rolodex&aop=menu';

        if (Current_User::allow('rolodex', 'edit_member')){
            $tags['new'] = array('title'=>dgettext('rolodex', 'New Member'),
                                 'link'=>$link);
        }
        $tags['list'] = array('title'=>dgettext('rolodex', 'List Members'),
                              'link'=>$link);

        if (Current_User::isUnrestricted('rolodex')) {
            $db = new PHPWS_DB('rolodex_member');
            $db->addWhere('active', 0);
            $unapproved = $db->count();
            $tags['approvals'] = array('title'=>sprintf(dgettext('rolodex', 'Unapproved (%s)'), $unapproved), 'link'=>$link);
            if (PHPWS_Settings::get('rolodex', 'enable_expiry')) {
                $db = new PHPWS_DB('rolodex_member');
                $db->addWhere('date_expires', time(), '<=');
                $expired = $db->count();
                $tags['expired'] = array('title'=>sprintf(dgettext('rolodex', 'Expired (%s)'), $expired), 'link'=>$link);
            }
        }

        if (Current_User::allow('rolodex', 'settings', null, null, true)){
            $tags['settings'] = array('title'=>dgettext('rolodex', 'Settings & Utilities'),
                                  'link'=>$link);
        }

        if (Current_User::isDeity()){
            $tags['info'] = array('title'=>dgettext('rolodex', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postSettings()
    {

        if (!empty($_POST['module_title'])) {
            PHPWS_Settings::set('rolodex', 'module_title', strip_tags($_POST['module_title']));
        } else {
            $errors[] = dgettext('rolodex', 'Please provide a module title.');
        }

        isset($_POST['allow_anon']) ?
        PHPWS_Settings::set('rolodex', 'allow_anon', 1) :
        PHPWS_Settings::set('rolodex', 'allow_anon', 0);

        PHPWS_Settings::set('rolodex', 'sortby', (int)$_POST['sortby']);

        isset($_POST['req_approval']) ?
        PHPWS_Settings::set('rolodex', 'req_approval', 1) :
        PHPWS_Settings::set('rolodex', 'req_approval', 0);

        isset($_POST['send_notification']) ?
        PHPWS_Settings::set('rolodex', 'send_notification', 1) :
        PHPWS_Settings::set('rolodex', 'send_notification', 0);

        isset($_POST['notify_all_saves']) ?
        PHPWS_Settings::set('rolodex', 'notify_all_saves', 1) :
        PHPWS_Settings::set('rolodex', 'notify_all_saves', 0);

        if (isset($_POST['admin_contact']) && ($_POST['admin_contact']) !== '') {
            if (PHPWS_Text::isValidInput($_POST['admin_contact'], 'email')) {
                PHPWS_Settings::set('rolodex', 'admin_contact', $_POST['admin_contact']);
            } else {
                $errors[] = dgettext('rolodex', 'Check your admin contact e-mail address for formatting errors.');
            }
        } else {
            PHPWS_Settings::set('rolodex', 'admin_contact', null);
        }

        isset($_POST['use_categories']) ?
        PHPWS_Settings::set('rolodex', 'use_categories', 1) :
        PHPWS_Settings::set('rolodex', 'use_categories', 0);

        isset($_POST['use_locations']) ?
        PHPWS_Settings::set('rolodex', 'use_locations', 1) :
        PHPWS_Settings::set('rolodex', 'use_locations', 0);

        isset($_POST['use_features']) ?
        PHPWS_Settings::set('rolodex', 'use_features', 1) :
        PHPWS_Settings::set('rolodex', 'use_features', 0);

        if (isset($_POST['comments_enable'])) {
            if (isset($_POST['comments_enforce'])) {
                PHPWS_Settings::set('rolodex', 'comments_enforce', 1);
                $this->setAllComments(1);
            } else {
                PHPWS_Settings::set('rolodex', 'comments_enforce', 0);
            }
            PHPWS_Settings::set('rolodex', 'comments_enable', 1);
            if (isset($_POST['comments_anon_enable'])) {
                if (isset($_POST['comments_anon_enforce'])) {
                    PHPWS_Settings::set('rolodex', 'comments_anon_enforce', 1);
                    $this->setAllComments_annon(1);
                } else {
                    PHPWS_Settings::set('rolodex', 'comments_anon_enforce', 0);
                }
                PHPWS_Settings::set('rolodex', 'comments_anon_enable', 1);
            } else {
                PHPWS_Settings::set('rolodex', 'comments_anon_enable', 0);
                PHPWS_Settings::set('rolodex', 'comments_anon_enforce', 0);
            }
        } else {
            PHPWS_Settings::set('rolodex', 'comments_enable', 0);
            PHPWS_Settings::set('rolodex', 'comments_enforce', 0);
            PHPWS_Settings::set('rolodex', 'comments_anon_enable', 0);
            PHPWS_Settings::set('rolodex', 'comments_anon_enforce', 0);
        }

        PHPWS_Settings::set('rolodex', 'contact_type', $_POST['contact_type']);
        PHPWS_Settings::set('rolodex', 'privacy_contact', $_POST['privacy_contact']);
        PHPWS_Settings::set('rolodex', 'privacy_web', $_POST['privacy_web']);
        PHPWS_Settings::set('rolodex', 'privacy_home_phone', $_POST['privacy_home_phone']);
        PHPWS_Settings::set('rolodex', 'privacy_bus_phone', $_POST['privacy_bus_phone']);
        PHPWS_Settings::set('rolodex', 'privacy_home', $_POST['privacy_home']);
        PHPWS_Settings::set('rolodex', 'privacy_business', $_POST['privacy_business']);
        PHPWS_Settings::set('rolodex', 'privacy_export', $_POST['privacy_export']);
        PHPWS_Settings::set('rolodex', 'privacy_use_search', $_POST['privacy_use_search']);

        PHPWS_Settings::set('rolodex', 'list_address', $_POST['list_address']);

        isset($_POST['list_phone']) ?
        PHPWS_Settings::set('rolodex', 'list_phone', 1) :
        PHPWS_Settings::set('rolodex', 'list_phone', 0);

        isset($_POST['list_categories']) ?
        PHPWS_Settings::set('rolodex', 'list_categories', 1) :
        PHPWS_Settings::set('rolodex', 'list_categories', 0);

        isset($_POST['list_locations']) ?
        PHPWS_Settings::set('rolodex', 'list_locations', 1) :
        PHPWS_Settings::set('rolodex', 'list_locations', 0);

        isset($_POST['list_features']) ?
        PHPWS_Settings::set('rolodex', 'list_features', 1) :
        PHPWS_Settings::set('rolodex', 'list_features', 0);


        isset($_POST['enable_expiry']) ?
        PHPWS_Settings::set('rolodex', 'enable_expiry', 1) :
        PHPWS_Settings::set('rolodex', 'enable_expiry', 0);

        if (isset($_POST['expiry_interval'])) {
            PHPWS_Settings::set('rolodex', 'expiry_interval', (int)$_POST['expiry_interval']);
        } else {
            PHPWS_Settings::reset('rolodex', 'expiry_interval');
        }

        isset($_POST['use_captcha']) ?
        PHPWS_Settings::set('rolodex', 'use_captcha', 1) :
        PHPWS_Settings::set('rolodex', 'use_captcha', 0);

        if ( !empty($_POST['max_img_width']) ) {
            $max_img_width = (int)$_POST['max_img_width'];
            if ($max_img_width >= 50 && $max_img_width <= 600 ) {
                PHPWS_Settings::set('rolodex', 'max_img_width', $max_img_width);
            }
        }

        if ( !empty($_POST['max_img_height']) ) {
            $max_img_height = (int)$_POST['max_img_height'];
            if ($max_img_height >= 50 && $max_img_height <= 600 ) {
                PHPWS_Settings::set('rolodex', 'max_img_height', $max_img_height);
            }
        }

        if ( !empty($_POST['max_thumb_width']) ) {
            $max_thumb_width = (int)$_POST['max_thumb_width'];
            if ($max_thumb_width >= 40 && $max_thumb_width <= 200 ) {
                PHPWS_Settings::set('rolodex', 'max_thumb_width', $max_thumb_width);
            }
        }

        if ( !empty($_POST['max_thumb_height']) ) {
            $max_thumb_height = (int)$_POST['max_thumb_height'];
            if ($max_thumb_height >= 40 && $max_thumb_height <= 200 ) {
                PHPWS_Settings::set('rolodex', 'max_thumb_height', $max_thumb_height);
            }
        }

        if ( !empty($_POST['other_img_width']) ) {
            $other_img_width = (int)$_POST['other_img_width'];
            if ($other_img_width >= 20 && $other_img_width <= 400 ) {
                PHPWS_Settings::set('rolodex', 'other_img_width', $other_img_width);
            }
        }

        if ( !empty($_POST['other_img_height']) ) {
            $other_img_height = (int)$_POST['other_img_height'];
            if ($other_img_height >= 20 && $other_img_height <= 400 ) {
                PHPWS_Settings::set('rolodex', 'other_img_height', $other_img_height);
            }
        }

        isset($_POST['show_block']) ?
        PHPWS_Settings::set('rolodex', 'show_block', 1) :
        PHPWS_Settings::set('rolodex', 'show_block', 0);

        PHPWS_Settings::set('rolodex', 'block_order_by_rand', $_POST['block_order_by_rand']);

        isset($_POST['block_on_home_only']) ?
        PHPWS_Settings::set('rolodex', 'block_on_home_only', 1) :
        PHPWS_Settings::set('rolodex', 'block_on_home_only', 0);

        if (!empty($_POST['custom1_name'])) {
            PHPWS_Settings::set('rolodex', 'custom1_name', PHPWS_Text::parseInput(strip_tags($_POST['custom1_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom1_name', null);
        }

        if (!empty($_POST['custom2_name'])) {
            PHPWS_Settings::set('rolodex', 'custom2_name', PHPWS_Text::parseInput(strip_tags($_POST['custom2_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom2_name', null);
        }

        if (!empty($_POST['custom3_name'])) {
            PHPWS_Settings::set('rolodex', 'custom3_name', PHPWS_Text::parseInput(strip_tags($_POST['custom3_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom3_name', null);
        }

        if (!empty($_POST['custom4_name'])) {
            PHPWS_Settings::set('rolodex', 'custom4_name', PHPWS_Text::parseInput(strip_tags($_POST['custom4_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom4_name', null);
        }

        if (!empty($_POST['custom5_name'])) {
            PHPWS_Settings::set('rolodex', 'custom5_name', PHPWS_Text::parseInput(strip_tags($_POST['custom5_name'])));
        } else {
            PHPWS_Settings::reset('rolodex', 'custom5_name');
        }

        if (!empty($_POST['custom6_name'])) {
            PHPWS_Settings::set('rolodex', 'custom6_name', PHPWS_Text::parseInput(strip_tags($_POST['custom6_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom6_name', null);
        }

        if (!empty($_POST['custom7_name'])) {
            PHPWS_Settings::set('rolodex', 'custom7_name', PHPWS_Text::parseInput(strip_tags($_POST['custom7_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom7_name', null);
        }

        if (!empty($_POST['custom8_name'])) {
            PHPWS_Settings::set('rolodex', 'custom8_name', PHPWS_Text::parseInput(strip_tags($_POST['custom8_name'])));
        } else {
            PHPWS_Settings::set('rolodex', 'custom8_name', null);
        }

        isset($_POST['custom1_list']) ?
        PHPWS_Settings::set('rolodex', 'custom1_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom1_list', 0);

        isset($_POST['custom2_list']) ?
        PHPWS_Settings::set('rolodex', 'custom2_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom2_list', 0);

        isset($_POST['custom3_list']) ?
        PHPWS_Settings::set('rolodex', 'custom3_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom3_list', 0);

        isset($_POST['custom4_list']) ?
        PHPWS_Settings::set('rolodex', 'custom4_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom4_list', 0);

        isset($_POST['custom5_list']) ?
        PHPWS_Settings::set('rolodex', 'custom5_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom5_list', 0);

        isset($_POST['custom6_list']) ?
        PHPWS_Settings::set('rolodex', 'custom6_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom6_list', 0);

        isset($_POST['custom7_list']) ?
        PHPWS_Settings::set('rolodex', 'custom7_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom7_list', 0);

        isset($_POST['custom8_list']) ?
        PHPWS_Settings::set('rolodex', 'custom8_list', 1) :
        PHPWS_Settings::set('rolodex', 'custom8_list', 0);


        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            PHPWS_Settings::save('rolodex');
            return true;
        }

    }


    public function postMember()
    {
        $this->loadMember();
        //        print_r($_POST); exit;

        if (isset($_POST['courtesy_title'])) {
            $this->member->setCourtesy_title($_POST['courtesy_title']);
        }

        if (isset($_POST['first_name'])) {
            $this->member->setFirst_name($_POST['first_name']);
        }

        if (isset($_POST['middle_initial'])) {
            $this->member->setMiddle_initial($_POST['middle_initial']);
        }

        if (isset($_POST['last_name'])) {
            $this->member->setLast_name($_POST['last_name']);
        }

        if (isset($_POST['honorific'])) {
            $this->member->setHonorific($_POST['honorific']);
        }

        if (isset($_POST['business_name'])) {
            $this->member->setBusiness_name($_POST['business_name']);
        }

        if (isset($_POST['department'])) {
            $this->member->setDepartment($_POST['department']);
        }

        if (isset($_POST['position_title'])) {
            $this->member->setPosition_title($_POST['position_title']);
        }

        if (isset($_POST['description'])) {
            $this->member->setDescription($_POST['description']);
        }

        if (isset($_POST['contact_email']) && ($_POST['contact_email']) !== '') {
            if (!$this->member->setContact_email($_POST['contact_email'])) {
                $errors[] = dgettext('rolodex', 'Check your contact e-mail address for formatting errors.');
            }
        } else {
            $this->member->contact_email = null;
        }

        if (!empty($_POST['website'])) {
            $link = PHPWS_Text::checkLink($_POST['website']);
            if (!$this->member->setWebsite($link)) {
                $errors[] = dgettext('rolodex', 'Check your website address for formatting errors.');
            }
        } else {
            $this->member->website = null;
        }

        if (isset($_POST['day_phone'])) {
            $this->member->setDay_phone($_POST['day_phone']);
        }

        if (isset($_POST['day_phone_ext'])) {
            $this->member->setDay_phone_ext($_POST['day_phone_ext']);
        }

        if (isset($_POST['evening_phone'])) {
            $this->member->setEvening_phone($_POST['evening_phone']);
        }

        if (isset($_POST['fax_number'])) {
            $this->member->setFax_number($_POST['fax_number']);
        }

        if (isset($_POST['tollfree_phone'])) {
            $this->member->setTollfree_phone($_POST['tollfree_phone']);
        }

        if (isset($_POST['mailing_address_1'])) {
            $this->member->setMailing_address_1($_POST['mailing_address_1']);
        }

        if (isset($_POST['mailing_address_2'])) {
            $this->member->setMailing_address_2($_POST['mailing_address_2']);
        }

        if (isset($_POST['mailing_city'])) {
            $this->member->setMailing_city($_POST['mailing_city']);
        }

        if (isset($_POST['mailing_state'])) {
            $this->member->setMailing_state($_POST['mailing_state']);
        }

        if (isset($_POST['mailing_country'])) {
            $this->member->setMailing_country($_POST['mailing_country']);
        }

        if (isset($_POST['mailing_zip_code'])) {
            $this->member->setMailing_zip_code($_POST['mailing_zip_code']);
        }

        if (isset($_POST['business_address_1'])) {
            $this->member->setBusiness_address_1($_POST['business_address_1']);
        }

        if (isset($_POST['business_address_2'])) {
            $this->member->setBusiness_address_2($_POST['business_address_2']);
        }

        if (isset($_POST['business_city'])) {
            $this->member->setBusiness_city($_POST['business_city']);
        }

        if (isset($_POST['business_state'])) {
            $this->member->setBusiness_state($_POST['business_state']);
        }

        if (isset($_POST['business_country'])) {
            $this->member->setBusiness_country($_POST['business_country']);
        }

        if (isset($_POST['business_zip_code'])) {
            $this->member->setBusiness_zip_code($_POST['business_zip_code']);
        }

        if (Current_User::allow('rolodex', 'edit_member')) {
            if (isset($_POST['active'])) {
                $this->member->setActive(1);
            } else {
                $this->member->setActive(0);
            }
        }

        $this->member->setPrivacy($_POST['privacy']);

        if (isset($_POST['email_privacy'])) {
            $this->member->setEmail_privacy(1);
        } else {
            $this->member->setEmail_privacy(0);
        }

        if (isset($_POST['custom1'])) {
            $this->member->setCustom1($_POST['custom1']);
        }

        if (isset($_POST['custom2'])) {
            $this->member->setCustom2($_POST['custom2']);
        }

        if (isset($_POST['custom3'])) {
            $this->member->setCustom3($_POST['custom3']);
        }

        if (isset($_POST['custom4'])) {
            $this->member->setCustom4($_POST['custom4']);
        }

        if (isset($_POST['custom5'])) {
            $this->member->setCustom5($_POST['custom5']);
        }

        if (isset($_POST['custom6'])) {
            $this->member->setCustom6($_POST['custom6']);
        }

        if (isset($_POST['custom7'])) {
            $this->member->setCustom7($_POST['custom7']);
        }

        if (isset($_POST['custom8'])) {
            $this->member->setCustom8($_POST['custom8']);
        }


        /* begin image stuff */
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $current_image = 'images/rolodex/' . $this->member->image['name'];
            $current_thumb = 'images/rolodex/' . $this->member->image['thumb_name'];
            $img_array = null;
            $image = new PHPWS_Image;
            $image->setDirectory('images/rolodex/');
            $image->setMaxWidth(PHPWS_Settings::get('rolodex', 'max_img_width'));
            $image->setMaxHeight(PHPWS_Settings::get('rolodex', 'max_img_height'));

            $prefix = sprintf('%s_%s_', $this->member->user_id, time());
            if (!$image->importPost('image', false, true, $prefix)) {
                if (isset($image->_errors)) {
                    foreach ($image->_errors as $oError) {
                        $errors[] = $oError->getMessage();
                    }
                }
            } elseif ($image->file_name) {
                $result = $image->write();
                if (PHPWS_Error::isError($result)) {
                    PHPWS_Error::log($result);
                    $errors[] = array(dgettext('rolodex', 'There was a problem saving your image.'));
                } else {
                    if ($current_image != $image->getPath() && is_file($current_image)) {
                        @unlink($current_image);
                        @unlink($current_thumb);
                    }
                    $img_array['name'] = $image->file_name;
                    $img_array['width'] = $image->width;
                    $img_array['height'] = $image->height;
                }
            }

            /* make the thumb */
            $source = $image->file_directory . $image->file_name;
            $new_file = preg_replace('/\.(jpg|jpeg|gif|png)$/i', '_tn.\\1', $image->file_name);
            $destination = $image->file_directory . $new_file;
            if (!PHPWS_File::scaleImage($source, $destination, PHPWS_Settings::get('rolodex', 'max_thumb_width'), PHPWS_Settings::get('rolodex', 'max_thumb_height'))) {
                $errors[] = array(dgettext('rolodex', 'There was a problem saving your thumbnail image.'));
            } else {
                $size = getimagesize($destination);
                $img_array['thumb_name'] = $new_file;
                $img_array['thumb_width'] = $size[0];
                $img_array['thumb_height'] = $size[1];
            }

            $this->member->setImage($img_array);
        } elseif (isset($_POST['clear_image'])) {
            $current_image = 'images/rolodex/' . $this->member->image['name'];
            $current_thumb = 'images/rolodex/' . $this->member->image['thumb_name'];
            if (is_file($current_image)) {
                @unlink($current_image);
            }
            if (is_file($current_thumb)) {
                @unlink($current_thumb);
            }
            $this->member->image = null;
        }
        /* end image stuff */


        if (Current_User::isUnrestricted('rolodex')) {
            if (isset($_POST['active'])) {
                $this->member->setActive(1);
            } else {
                $this->member->setActive(0);
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_member');
            return false;
        } else {
            return true;
        }

    }


    public function postLocation()
    {
        $this->loadLocation();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('rolodex', 'You must give this location a title.');
        } else {
            $this->location->setTitle($_POST['title']);
        }

        if (isset($_POST['description'])) {
            $this->location->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->location->setImage_id((int)$_POST['image_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }

    }


    public function postFeature()
    {
        $this->loadFeature();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('rolodex', 'You must give this feature a title.');
        } else {
            $this->feature->setTitle($_POST['title']);
        }

        if (isset($_POST['description'])) {
            $this->feature->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->feature->setImage_id((int)$_POST['image_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }

    }


    public function checkMessage()
    {
        $this->loadMember();

        if (!empty($_POST['name'])) {
            $_POST['name'] = strip_tags($_POST['name']);
        } else {
            $errors[] = dgettext('rolodex', 'Please provide your name.');
        }

        if (!empty($_POST['email'])) {
            if (PHPWS_Text::isValidInput($_POST['email'], 'email')) {
                $_POST['email'] = $_POST['email'];
            } else {
                $errors[] = dgettext('rolodex', 'Your email address is improperly formatted.');
            }
        } else {
            $errors[] = dgettext('rolodex', 'Please provide your email address.');
        }

        if (!empty($_POST['subject'])) {
            $_POST['subject'] = strip_tags($_POST['subject']);
        } else {
            $errors[] = dgettext('rolodex', 'Please provide a subject.');
        }

        if (!empty($_POST['message'])) {
            $_POST['message'] = strip_tags($_POST['message']);
        } else {
            $errors[] = dgettext('rolodex', 'Please provide a message.');
        }

        if (!Rolodex::confirm()) {
            $errors['CONFIRM_ERROR'] = dgettext('rolodex', 'Confirmation phrase is not correct.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }


    function confirm()
    {
        if (!PHPWS_Settings::get('rolodex', 'use_captcha') ||
        !extension_loaded('gd')) {
            return true;
        }

        PHPWS_Core::initCoreClass('Captcha.php');
        return Captcha::verify();
    }


    public function sendMail()
    {
        $this->loadMember();

        $url = PHPWS_Core::getHomeHttp();
        $from = $_POST['name'];
        $sender = $_POST['email'];
        $sendto = $this->member->getDisplay_email();
        $subject = $_POST['subject'];
        $message = sprintf(dgettext('rolodex', 'This message from %s was sent via %s.'), $from, $url) . "\n\n";
        $message .= $_POST['message'];

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        $mail->addSendTo($sendto);
        $mail->setSubject($subject);
        $mail->setFrom(sprintf('%s<%s>', $from, $sender));
        $mail->setMessageBody($message);

        return $mail->send();

    }


    public function resetExpired($interval)
    {
        $expires = mktime(0, 0, 0, date("m"), date("d")+$interval, date("Y"));
        $db = new PHPWS_DB('rolodex_member');
        $db->addValue('date_expires', $expires);
        PHPWS_Error::logIfError($db->update());
    }


    public function deleteExpired()
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
        $db = new PHPWS_DB('rolodex_member');
        $db->addWhere('date_expires', time(), '<=');
        $expired = $db->getObjects('Rolodex_Member');
        $num = count($expired);
        if ($expired) {
            foreach ($expired as $member) {
                $member->deleteMember();
            }
        }
        return $num;
    }


    public function setAllComments($num)
    {
        $db = new PHPWS_DB('rolodex_member');
        $db->addValue('allow_comments', $num);
        PHPWS_Error::logIfError($db->update());
    }


    public function setAllComments_annon($num)
    {
        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
        $db = new PHPWS_DB('rolodex_member');
        $result = $db->getObjects('Rolodex_Member');
        if ($result) {
            foreach ($result as $member) {
                $member->setAllow_anon($num);
                PHPWS_Core::initModClass('comments', 'Comments.php');
                $thread = Comments::getThread($member->key_id);
                $thread->allowAnonymous($num);
                $thread->save();
            }
        }
        $db->addValue('allow_anon', $num);
        PHPWS_Error::logIfError($db->update());
    }



    public function search_index_all()
    {

        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
        $db = new PHPWS_DB('rolodex_member');
        $db->addColumn('demographics.user_id');
        $db->addColumn('demographics.first_name');
        $db->addColumn('demographics.last_name');
        $db->addColumn('demographics.business_name');
        $db->addColumn('rolodex_member.description');
        $db->addColumn('rolodex_member.key_id');
        $db->addWhere('rolodex_member.user_id', 'demographics.user_id');
        $db->addOrder('demographics.user_id');
        $result = $db->select();
        if (!empty($result)) {
            if (PHPWS_Error::logIfError($result)) {
                return false;
            }
            foreach ($result as $member) {
                $search = new Search($member['key_id']);
                $search->resetKeywords();
                $name = $member['first_name'] . ' ' . $member['last_name'];
                $search->addKeywords($name);
                $search->addKeywords($member['business_name']);
                $search->addKeywords($member['description']);
                PHPWS_Error::logIfError($search->save());
            }
        }
        return true;
    }


    public function search_remove_all()
    {

        PHPWS_Core::initModClass('search', 'Search.php');
        $db = new PHPWS_DB('search');
        $db->addWhere('module', 'rolodex');
        $result = $db->delete();

        if (!empty($result)) {
            if (PHPWS_Error::logIfError($result)) {
                return false;
            }
        }
        return true;
    }


    public function exportCSV($approved=null, $expired=false)
    {

        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');

        $content = null;
        $content .= Rolodex_Member::printCSVHeader();

        $db = new PHPWS_DB('rolodex_member');
        $db->addColumn('demographics.user_id');
        $db->addColumn('rolodex_member.user_id');
        $db->addWhere('rolodex_member.user_id', 'demographics.user_id');

        if (isset($approved)) {
            $db->addWhere('active', $approved);
        }

        if (PHPWS_Settings::get('rolodex', 'enable_expiry')) {
            if ($expired) {
                $db->addWhere('date_expires', time(), '<=');
            } else {
                $db->addWhere('date_expires', time(), '>=');
            }
        }

        if (!Current_User::isUnrestricted('rolodex')) {
            $db->addWhere('active', 1);
        }

        if (!$_SESSION['User']->id) {
            $db->addWhere('rolodex_member.privacy', 0);
        } elseif (!Current_User::allow('rolodex', 'view_privates')) {
            $db->addWhere('rolodex_member.privacy', 1, '<=');
        }

        if (PHPWS_Settings::get('rolodex', 'sortby')) {
            $db->addOrder('demographics.last_name', 'asc');
            $db->addOrder('demographics.first_name', 'asc');
            $db->addOrder('demographics.business_name', 'asc');
        } else {
            $db->addOrder('demographics.business_name', 'asc');
            $db->addOrder('demographics.last_name', 'asc');
            $db->addOrder('demographics.first_name', 'asc');
        }

        $result = $db->select();

        if ($result) {
            foreach($result as $row) {
                $member = new Rolodex_Member($row['user_id']);
                if ($member->isMemberVisible()) {
                    $content .= $member->printCSV();
                }
            }
        }

        $filename = 'rolodex' . date('Ymd') . '.csv';
        Header('Content-Disposition: attachment; filename=' . $filename);
        Header('Content-Length: ' . strlen($content));
        Header('Connection: close');
        Header('Content-Type: text/plain; name=' . $filename);
        echo $content;
        exit();
    }


    public function navLinks()
    {

        $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Browse members'), 'rolodex', array('uop'=>'list'));

        if (PHPWS_Settings::get('rolodex', 'use_categories')) {
            $db = new PHPWS_DB('category_items');
            $db->addWhere('module', 'rolodex');
            $categories = $db->count();
            if ($categories > 0) {
                $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Categories'), "rolodex",  array('uop'=>'categories'));
            }
        }

        if (PHPWS_Settings::get('rolodex', 'use_locations')) {
            $db = new PHPWS_DB('rolodex_location');
            $locations = $db->count();
            if ($locations > 0) {
                $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Locations'), "rolodex",  array('uop'=>'locations'));
            }
        }

        if (PHPWS_Settings::get('rolodex', 'use_features')) {
            $db = new PHPWS_DB('rolodex_feature');
            $features = $db->count();
            if ($features > 0) {
                $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Features'), "rolodex",  array('uop'=>'features'));
            }
        }

        if ($categories > 0 || $locations > 0 || $features > 0) {
            $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Advanced'), "rolodex",  array('uop'=>'advanced'));
        }

        if (Current_User::allow('rolodex', 'settings', null, null, true) && !isset($_REQUEST['aop'])){
            $links[] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Settings'), "rolodex",  array('aop'=>'menu', 'tab'=>'settings'));
        }

        return $links;
    }

    public function alpha_click()
    {

        $alphabet = $this->alphabet();
        $alpha = array();
        $links = array();
        foreach ($alphabet as $alphachar) {
            if (@$_REQUEST['uop'] == "list") {
                $vars['uop'] = 'list';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "rolodex", $vars) . "\n";
            } elseif (@$_REQUEST['aop'] == "list_expired") {
                $vars['aop'] = 'list_expired';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "rolodex", $vars) . "\n";
            } else {
                $vars['uop'] = 'list';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "rolodex", $vars) . "\n";
            }
        }

        if (@$_REQUEST['uop'] == "list") {
            $vars['uop'] = 'list';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'Other'), "rolodex",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'All'), "rolodex",  array('uop'=>'list')) . "\n";
        } elseif (@$_REQUEST['aop'] == "list_expired") {
            $vars['aop'] = 'list_expired';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'Other'), "rolodex",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'All'), "rolodex",  array('aop'=>'list_expired')) . "\n";
        } else {
            $vars['uop'] = 'list';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'Other'), "rolodex",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('rolodex', 'All'), "rolodex",  array('uop'=>'list')) . "\n";
        }

        $links = $this->navLinks();

        $tpl['LIST'] = implode(' | ', $alpha);
        $tpl['LINKS'] = implode(' | ', $links);
        return PHPWS_Template::process($tpl, 'rolodex', 'alpha_click.tpl');

    }


    /**
     * Creates an array of the English alphabet
     *
     * If '$letter_case' is lower then the character set
     * will be lowercase. If it is NULL, then uppercase.
     * Needs internationalization
     *
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @param  string $letter_case Indicates to return an uppercase or lowercase array
     * @return array  $ret_array   Numerically indexed array of alphabet
     * @access public
     */
    public function alphabet($letter_case=NULL)
    {
        if ($letter_case == "lower") {
            $start = ord("a");
            $end = ord("z");
        } else {
            $start = ord("A");
            $end = ord("Z");
        }

        for ($i=$start;$i<=$end;$i++)
        $ret_array[] = chr($i);

        return $ret_array;
    }


    /**
     * Returns a form for module inclusion
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @modified Verdon Vaillancourt
     * @access public
     */
    public function getItemForm($type='location', $match=null, $select_name='location', $multiple=true)
    {

        switch($type) {
            case 'location':
                PHPWS_Core::initModClass('rolodex', 'RDX_Location.php');
                $db = new PHPWS_DB('rolodex_location');
                $db->addOrder('title asc');
                $result = $db->getObjects('Rolodex_Location');
                break;
            case 'feature':
                PHPWS_Core::initModClass('rolodex', 'RDX_Feature.php');
                $db = new PHPWS_DB('rolodex_feature');
                $db->addOrder('title asc');
                $result = $db->getObjects('Rolodex_Feature');
                break;
        }

        $items = null;
        if ($result) {
            foreach ($result as $item) {
                $items[$item->id] = $item->title;
            }
        }

        if ($multiple) {
            if (javascriptEnabled()) {
                $vars['id'] = 'cid-' . rand();
                $vars['select_name'] = $select_name;
                $vars['options'] = $items;
                if (!empty($match) && is_array($match)) {
                    $vars['match'] = $match;
                }
                return javascript('multiple_select', $vars);
            } else {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            $form = new PHPWS_Form;
            $form->addSelect($select_name, $items);
            if (!empty($match) && is_string($match)) {
                $form->setMatch($select_name, $match);
            }
            return $form->get($select_name);
        }

    }


    public function getItemSelect($type='location', $match=null, $select_name='location', $multiple=true, $count=true)
    {

        switch($type) {
            case 'location':
                PHPWS_Core::initModClass('rolodex', 'RDX_Location.php');
                $db = new PHPWS_DB('rolodex_location');
                $db->addOrder('title asc');
                $result = $db->getObjects('Rolodex_Location');
                break;
            case 'feature':
                PHPWS_Core::initModClass('rolodex', 'RDX_Feature.php');
                $db = new PHPWS_DB('rolodex_feature');
                $db->addOrder('title asc');
                $result = $db->getObjects('Rolodex_Feature');
                break;
        }

        if ($result) {
            foreach ($result as $item) {
                if ($count) {
                    $db = new PHPWS_DB('rolodex_'.$type.'_items');
                    $db->addWhere($type.'_id', $item->id);
                    $qty = $db->count();
                    $items[$item->id] = $item->title . ' ('.$qty.')';
                } else {
                    $items[$item->id] = $item->title;
                }
            }
        }

        if ($items) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('rolodex', 'No choices configured.');
        }

    }


    public function getCatSelect($match=null, $select_name='categories', $multiple=true, $count=true)
    {

        PHPWS_Core::initModClass('categories', 'Category.php');
        $db = new PHPWS_DB('categories');
        $db->addOrder('title asc');

        $result = $db->getObjects('Category');

        if ($result) {
            foreach ($result as $item) {
                if ($count) {
                    $db = new PHPWS_DB('category_items');
                    $db->addWhere('cat_id', $item->id);
                    $db->addWhere('module', 'rolodex');
                    $qty = $db->count();
                    $items[$item->id] = $item->title . ' ('.$qty.')';
                } else {
                    $items[$item->id] = $item->title;
                }
            }
        }

        if ($items) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('rolodex', 'No choices configured.');
        }

    }






}

?>