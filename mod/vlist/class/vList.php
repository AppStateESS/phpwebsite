<?php
/**
    * vlist - phpwebsite module
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

PHPWS_Core::requireConfig('vlist');

class vList {
    public $forms      = null;
    public $panel      = null;
    public $title      = null;
    public $message    = null;
    public $content    = null;
    public $listing    = null;
    public $group     = null;
    public $element    = null;


    public function adminMenu($action=null)
    {
        if (!Current_User::allow('vlist')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['aop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['aop'];
        }
        $this->loadMessage();

        /* This switch determines if 'settings' panel needs creating */
        switch($action) {
            case 'post_settings':
            case 'reset_expired':
            case 'delete_expired':
            case 'search_index_all':
            case 'search_remove_all':
            case 'new_group':
            case 'edit_group':
            case 'post_group':
            case 'delete_group':
            case 'new_element':
            case 'edit_element':
            case 'post_element':
            case 'delete_element':
            case 'edit_options':
            case 'post_options':
            case 'delete_option':
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $settingsPanel = vList_Forms::settingsPanel();
                $settingsPanel->enableSecure();
                break;
            case 'menu':
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'groups' || $_GET['tab'] == 'elements') {
                        PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                        $settingsPanel = vList_Forms::settingsPanel();
                        $settingsPanel->enableSecure();
                    }
                }
    
        }

        /* This switch dumps the content in */
        switch($action) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('listings');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'new_listing':
            case 'edit_listing':
                if (!Current_User::authorized('vlist', 'edit_listing')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_listing');
                break;
    
            case 'post_listing':
                if (!Current_User::authorized('vlist', 'edit_listing')) {
                    Current_User::disallow();
                }
                if ($this->postListing()) {
                    if (PHPWS_Error::logIfError($this->listing->save())) {
                        $this->forwardMessage(dgettext('vlist', 'Error occurred when saving listing.'));
                        PHPWS_Core::reroute('index.php?module=vlist&aop=menu');
                    } else {
                        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
                            if ($this->postExtras()) {
                                $this->forwardMessage(dgettext('vlist', 'Listing saved successfully.'));
                                PHPWS_Core::reroute('index.php?module=vlist&aop=menu');
                            } else {
                                $this->loadForm('edit_listing');
                            }
                        } else {
                            $this->forwardMessage(dgettext('vlist', 'Listing saved successfully.'));
                            PHPWS_Core::reroute('index.php?module=vlist&aop=menu');
                        }
                    }
                } else {
                    $this->loadForm('edit_listing');
                }
                break;
    
            case 'delete_listing':
                if (!Current_User::authorized('vlist', 'delete_listing')) {
                    Current_User::disallow();
                }
                $this->loadListing();
                $this->listing->delete();
                $this->message = dgettext('vlist', 'Listing deleted.');
                $this->loadForm('listings');
                break;
                
            case 'activate_listing':
                if (!Current_User::authorized('vlist', 'edit_listing')) {
                    Current_User::disallow();
                }
                $this->loadListing();
                $this->listing->active = 1;
                $this->listing->saveListing();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Listing %s activated.'), $this->listing->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=listings');
                break;
    
            case 'deactivate_listing':
                if (!Current_User::authorized('vlist', 'edit_listing')) {
                    Current_User::disallow();
                }
                $this->loadListing();
                $this->listing->active = 0;
                $this->listing->saveListing();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Listing %s deactivated.'), $this->listing->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=listings');
                break;
    

            case 'new_group':
            case 'edit_group':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('groups');
                $this->loadForm('edit_group');
                break;

            case 'post_group':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('groups');
                if ($this->postGroup()) {
                    if (PHPWS_Error::logIfError($this->group->save())) {
                        $this->forwardMessage(dgettext('vlist', 'Error occurred when saving group.'));
                        PHPWS_Core::reroute('index.php?module=vlist&aop=edit_group&group=' . $this->group->id);
                    } else {
                        $this->forwardMessage(dgettext('vlist', 'Group saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=groups');
                    }
                } else {
                    $this->loadForm('edit_group');
                }
                break;

            case 'delete_group':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('groups');
                $this->loadGroup();
                $this->group->delete();
                $this->message = dgettext('vlist', 'Group deleted.');
                $this->loadForm('groups');
                break;
            
            case 'new_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
                if(isset($_POST['type']) && ($_POST['type'] != '0' || $_POST['type'] != '00')) {
                    if ($_POST['type'] == 'Link' || $_POST['type'] == 'GPS' || $_POST['type'] == 'Email') {
                        $type = 'Textfield';
                    } else {
                        $type = $_POST['type'];
                    }
                } else {
                    $this->forwardMessage(dgettext('vlist', 'You must select a valid element type to add.'));
                    PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                }
                $class = 'UNI_' . $type;
                PHPWS_Core::initModClass('vlist', 'elements/' . $class . '.php');
                $this->element = new $class;
                $this->element->vlist = & $this;
                $this->title = sprintf(dgettext('vlist', 'Add/edit %s custom element'), $_POST['type']);
                $this->content = $this->element->edit();
                break;

            case 'edit_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
                $this->loadElement();
                $this->element->vlist = & $this;
                $this->title = sprintf(dgettext('vlist', 'Add/edit %s custom element'), $this->element->type);
                $this->content = $this->element->edit();
                break;

            case 'post_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
                $this->loadElement();
                $this->element->vlist = & $this;
                if ($this->element->type == 'Checkbox' || $this->element->type == 'Dropbox' || $this->element->type == 'Radiobutton' || $this->element->type == 'Multiselect') {
                    $this->title = sprintf(dgettext('vlist', 'Add/edit %s options'), $this->element->type);
                } else {
                    $this->title = sprintf(dgettext('vlist', 'Save %s custom element'), $this->element->type);
                }
                $this->content = $this->element->save();
                break;

            case 'delete_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
                $this->loadElement();
                $this->element->vlist = & $this;
                $this->element->delete();
                $this->message = dgettext('vlist', 'Element deleted.');
                $this->loadForm('elements');
                break;
            
            case 'activate_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->active = 1;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s activated.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'deactivate_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->active = 0;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s deactivated.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'list_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->list = 1;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s enabled in list.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'delist_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->list = 0;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s disabled in list.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'search_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->search = 1;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s enabled in search.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'desearch_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->search = 0;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s disabled in search.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;

            case 'private_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->private = 1;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s is now restricted.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;
    
            case 'deprivate_element':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $this->loadElement();
                $this->element->private = 0;
                $this->element->saveElement();
                $this->forwardMessage(sprintf(dgettext('vlist', 'Element %s is now public.'), $this->element->getTitle(true)));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                break;

            case 'edit_options':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
                $this->loadElement();
                $this->element->vlist = & $this;
                $this->title = sprintf(dgettext('vlist', 'Add/edit %s options'), $this->element->type);
                $this->content = $this->element->editOptions();
                break;

            case 'post_options':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
//print_r($_POST); exit;
                $this->loadElement();
                $this->element->vlist = & $this;
                $this->title = sprintf(dgettext('vlist', 'Save %s options'), $this->element->type);
                $this->content = $this->element->saveOptions();
                break;

            case 'delete_option':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                $settingsPanel->setCurrentTab('elements');
//print_r($_REQUEST); exit;
                $this->loadElement();
                $this->element->vlist = & $this;
                $this->title = sprintf(dgettext('vlist', 'Delete %s options'), $this->element->type);
                $this->content = $this->element->deleteOption($_REQUEST['option_id']);
                break;

            case 'post_settings':
                if (!Current_User::authorized('vlist', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('vlist', 'Listing settings saved.'));
                    PHPWS_Core::reroute('index.php?module=vlist&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        /* This switch creates the 'settings' panel when needed */
        switch($action) {
            case 'post_settings':
            case 'reset_expired':
            case 'delete_expired':
            case 'search_index_all':
            case 'search_remove_all':
            case 'new_group':
            case 'edit_group':
            case 'post_group':
            case 'delete_group':
            case 'new_element':
            case 'edit_element':
            case 'post_element':
            case 'delete_element':
            case 'edit_options':
            case 'post_options':
            case 'delete_option':
                $settingsPanel->setContent($this->content);
                $this->content = $settingsPanel->display();
            case 'menu':
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'groups' || $_GET['tab'] == 'elements') {
                        $settingsPanel->setContent($this->content);
                        $this->content = $settingsPanel->display();
                    }
                }
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'vlist', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'vlist', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
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

        switch($action) {

            case 'submit_listing':
            case 'edit_listing':
//print_r($_SESSION['User']); exit;
//print_r($_SESSION['User']->username); exit;
//                if (!PHPWS_Settings::get('vlist', 'user_submissions')) {
//                if (!PHPWS_Settings::get('vlist', 'anon_submissions') && !(PHPWS_Settings::get('vlist', 'user_submissions') && $_SESSION['User']->username == '')) {
                if (PHPWS_Settings::get('vlist', 'anon_submissions') || (PHPWS_Settings::get('vlist', 'user_submissions') && $_SESSION['User']->username != '')) {
                    $this->loadForm('edit_listing');
                } else {
                    Current_User::disallow();
                }
                break;

            case 'post_listing':
//                if (!PHPWS_Settings::get('vlist', 'user_submissions')) {
                if (PHPWS_Settings::get('vlist', 'anon_submissions') || (PHPWS_Settings::get('vlist', 'user_submissions') && $_SESSION['User']->username != '')) {
                    if ($this->postListing()) {
                        if (PHPWS_Error::logIfError($this->listing->save())) {
                            $this->forwardMessage(dgettext('vlist', 'Error occurred when submitting listing.'));
                            PHPWS_Core::reroute('index.php?module=vlist&uop=listings');
                        } else {
                            if ($this->postExtras()) {
                                $this->forwardMessage(dgettext('vlist', 'Listing submitted for review successfully.'));
                                PHPWS_Core::reroute('index.php?module=vlist&uop=listings');
                            } else {
                                $this->loadForm('edit_listing');
                            }
                        }
                    } else {
                        $this->loadForm('edit_listing');
                    }
                } else {
                    Current_User::disallow();
                }
                break;
    
            case 'listings':
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->listListings(1);
                break;

            case 'view_listing':
                $this->loadListing();
                if (!Current_User::isUnrestricted('vlist') && !$this->listing->active) {
                    $this->title = dgettext('vlist', 'Inactive listing');
                    $this->content = dgettext('vlist', 'Sorry, this listing is not currently available.');
                    $this->content .= '<br />' . $this->listing->links();
                } else {
                    Layout::addPageTitle($this->listing->getTitle());
                    $this->title = $this->listing->getTitle(true);
                    if (Current_User::isUnrestricted('vlist') && !$this->listing->active) {
                        $this->title = dgettext('vlist', 'INACTIVE');
                    }
                    $this->content = $this->listing->view();
                }
                break;

            case 'groups':
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->listGroups();
                break;
    
            case 'view_group':
                if (isset($_REQUEST['group_id'])) {
                    $id = $_REQUEST['group_id'];
                } elseif (isset($_REQUEST['group'])) {
                    $id = $_REQUEST['group'];
                } elseif (isset($_REQUEST['id'])) {
                    $id = $_REQUEST['id'];
                } 
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->listListings(1, $id);
                break;

            case 'view_owner':
                if (isset($_REQUEST['owner_id'])) {
                    $id = $_REQUEST['owner_id'];
                } elseif (isset($_REQUEST['owner'])) {
                    $id = $_REQUEST['owner'];
                } elseif (isset($_REQUEST['id'])) {
                    $id = $_REQUEST['id'];
                } 
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->listListings(1, null, $id);
                break;

            /* not sure if this is being used */
            case 'view_element':
                $this->loadElement();
                Layout::addPageTitle($this->element->getTitle());
                $this->title = $this->element->getTitle(true);
                $this->content = $this->element->view();
                break;

            case 'advanced':
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->advSearchForm();
                break;
    
            case 'adv_search':
                PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
                $this->forms = new vList_Forms;
                $this->forms->vlist = & $this;
                $this->forms->listListings(1);
                break;
    
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'vlist', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'vlist', 'main_user.tpl'));
        }
        
   }


    public function forwardMessage($message, $title=null)
    {
        $_SESSION['vList_Message']['message'] = $message;
        if ($title) {
            $_SESSION['vList_Message']['title'] = $title;
        }
    }
    

    public function loadMessage()
    {
        if (isset($_SESSION['vList_Message'])) {
            $this->message = $_SESSION['vList_Message']['message'];
            if (isset($_SESSION['vList_Message']['title'])) {
                $this->title = $_SESSION['vList_Message']['title'];
            }
            PHPWS_Core::killSession('vList_Message');
        }
    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('vlist', 'vList_Forms.php');
        $this->forms = new vList_Forms;
        $this->forms->vlist = & $this;
        $this->forms->get($type);
    }


    public function loadListing($id=0)
    {
        PHPWS_Core::initModClass('vlist', 'vList_Listing.php');

        if ($id) {
            $this->listing = new vList_Listing($id);
        } elseif (isset($_REQUEST['listing_id'])) {
            $this->listing = new vList_Listing($_REQUEST['listing_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->listing = new vList_Listing($_REQUEST['id']);
        } elseif (isset($_REQUEST['listing'])) {
            $this->listing = new vList_Listing($_REQUEST['listing']);
        } else {
            $this->listing = new vList_Listing;
        }
    }


    public function loadGroup($id=0)
    {
        PHPWS_Core::initModClass('vlist', 'vList_Group.php');

        if ($id) {
            $this->group = new vList_Group($id);
        } elseif (isset($_REQUEST['group_id'])) {
            $this->group = new vList_Group($_REQUEST['group_id']);
        } elseif (isset($_REQUEST['group'])) {
            $this->group = new vList_Group($_REQUEST['group']);
        } elseif (isset($_REQUEST['id'])) {
            $this->group = new vList_Group($_REQUEST['id']);
        } else {
            $this->group = new vList_Group;
        }
    }


    public function loadElement($id=0)
    {

        if ($id) {
            $id = $id;
        } elseif (isset($_REQUEST['element_id'])) {
            $id = $_REQUEST['element_id'];
        } elseif (isset($_REQUEST['element'])) {
            $id = $_REQUEST['element'];
        } elseif (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
        } else {
            if(isset($_REQUEST['type'])) {
                if ($_REQUEST['type'] == 'Link' || $_REQUEST['type'] == 'GPS' || $_REQUEST['type'] == 'Email') {
                    $type = 'Textfield';
                } else {
                    $type = $_REQUEST['type'];
                }
            } else {
                $this->forwardMessage(dgettext('vlist', 'The type of element was not specified.'));
                PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
            }
            $class = 'UNI_' . $type;
            PHPWS_Core::initModClass('vlist', 'elements/' . $class . '.php');
            $this->element = new $class;
        }
        
        $db = new PHPWS_DB('vlist_element');
        $db->addWhere('id', $id);
        $db->addColumn('type');
        $result = $db->select('one');
        if ($result) {
            if ($result == 'Link' || $result == 'GPS' || $result == 'Email') {
                $type = 'Textfield';
            } else {
                $type = $result;
            }
        } else {
            if ($_REQUEST['type'] == 'Link' || $_REQUEST['type'] == 'GPS' || $_REQUEST['type'] == 'Email') {
                $type = 'Textfield';
            } else {
                $type = $_REQUEST['type'];
            }
        }
        $class = 'UNI_' . $type;
        PHPWS_Core::initModClass('vlist', 'elements/' . $class . '.php');
        $this->element = new $class($id);

    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('vlist-panel');
        $link = 'index.php?module=vlist&aop=menu';
        
        if (Current_User::allow('vlist', 'edit_listing')) {
            $tags['new_listing'] = array('title'=>dgettext('vlist', 'New Listing'),
                                 'link'=>$link);
        }
            $tags['listings'] = array('title'=>dgettext('vlist', 'All Listings'),
                                  'link'=>$link);

        if (Current_User::isUnrestricted('vlist')) {
            $db = new PHPWS_DB('vlist_listing');
            $db->addWhere('active', 0);
            $unapproved = $db->count();
            $tags['approvals'] = array('title'=>sprintf(dgettext('vlist', 'Unapproved (%s)'), $unapproved), 'link'=>$link);
        }

        if (Current_User::allow('vlist', 'settings', null, null, true)) {
            $tags['settings'] = array('title'=>dgettext('vlist', 'Settings & Fields'),
                                  'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('vlist', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postListing()
    {
        $this->loadListing();

        if (PHPWS_Settings::get('vlist', 'enable_users')) {
            $this->listing->owner_id = (int)$_POST['owner_id'];
        }

        if (empty($_POST['title'])) {
            $errors[] = dgettext('vlist', 'You must give this listing a title.');
        } else {
            $this->listing->setTitle($_POST['title']);
        }

        isset($_POST['active']) ?
            $this->listing->active = 1 :
            $this->listing->active = 0 ;

        if (empty($_POST['description'])) {
            $errors[] = dgettext('vlist', 'You must give this listing a description.');
        } elseif ($_POST['description'] == '<p>&#160;</p>') {
            $errors[] = dgettext('vlist', 'You must give this listing a description.');
        } else {
            $this->listing->setDescription($_POST['description']);
        }

        if (isset($_POST['file_id'])) {
            $this->listing->setFile_id((int)$_POST['file_id']);
        }

        if (isset($_POST['image_id'])) {
            $this->listing->setImage_id((int)$_POST['image_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_listing');
            return false;
        } else {
            return true;
        }
    }


    public function postExtras()
    {

        /* first delete all the existing element items for this listing */
        $db = new PHPWS_DB('vlist_element_items');
        $db->addWhere('listing_id', $this->listing->id);
        $db->delete();

        /* now get all the active elements */
        $db = new PHPWS_DB('vlist_element');
        $db->addWhere('active', 1);
        $db->addOrder('sort asc');
        $result = $db->select();

        /* if there are any */
        if ($result) {
            $db = new PHPWS_DB('vlist_element_items');
            $db->addValue('listing_id', $this->listing->id);

            /* then loop through them */
            foreach ($result as $element) {
                $db = new PHPWS_DB('vlist_element_items');
                $db->addValue('listing_id', $this->listing->id);
                $db->addValue('element_id', $element['id']);

                /* check if it's req */
                if ($element['required'] == 1) {

                    /* if it is, check it's set (and set it) or return an error */
                    if ($element['type'] == 'Checkbox' || $element['type'] == 'Multiselect') {
                        if (isset($_POST['UNI_'.$element['id']])) {
                            foreach($_POST['UNI_'.$element['id']] as $option) {
                                $db->addValue('option_id', $option);
                                $db->insert();
                            }
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must make at least one selection for %s.'), $element['title']);
                        }
                    } elseif ($element['type'] == 'Dropbox' || $element['type'] == 'Radiobutton') {
                        if (isset($_POST['UNI_'.$element['id']])) {
                            $db->addValue('option_id', $_POST['UNI_'.$element['id']]);
                            $db->insert();
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must make a selection for %s.'), $element['title']);
                        }
                    } elseif ($element['type'] == 'Link') {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $link = PHPWS_Text::checkLink(strip_tags($_POST['UNI_'.$element['id']]));
                            if (PHPWS_Text::isValidInput($link, 'url')) {
                                $db->addValue('value', $link);
                                $db->insert();
                            } else {
                                $errors[] = sprintf(dgettext('vlist', 'Check the link address for %s, for formatting errors.'), $element['title']);
                            }
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must enter a link for %s.'), $element['title']);
                        }
                    } elseif ($element['type'] == 'GPS') {
                        if (!empty($_POST['UNI_'.$element['gps']])) {
                            $gps = strip_tags($_POST['UNI_'.$element['id']]);
//    NEED A REGEX HERE                        if (PHPWS_Text::isValidInput($gps, 'url')) {
                                $db->addValue('value', $gps);
                                $db->insert();
//                            } else {
//                                $errors[] = sprintf(dgettext('vlist', 'Check the value for %s, for formatting errors.'), $element['title']);
//                            }
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must enter a gps value for %s.'), $element['title']);
                        }
                    } elseif ($element['type'] == 'Email') {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $email = strip_tags($_POST['UNI_'.$element['id']]);
                            if (PHPWS_Text::isValidInput($email, 'email')) {
                                $db->addValue('value', $email);
                                $db->insert();
                            } else {
                                $errors[] = sprintf(dgettext('vlist', 'Check the email address for %s, for formatting errors.'), $element['title']);
                            }
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must enter an email address for %s.'), $element['title']);
                        }
                    } else {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $db->addValue('value', PHPWS_Text::parseInput($_POST['UNI_'.$element['id']]));
                            $db->insert();
                        } else {
                            $errors[] = sprintf(dgettext('vlist', 'You must enter something for %s.'), $element['title']);
                        }
                    }
                
                } else {

                    /* if it's not req, set it or null it */
                    if ($element['type'] == 'Checkbox' || $element['type'] == 'Multiselect') {
                        if (isset($_POST['UNI_'.$element['id']])) {
                            foreach($_POST['UNI_'.$element['id']] as $option) {
                                $db->addValue('option_id', $option);
                                $db->insert();
                            }
                        }
                    } elseif ($element['type'] == 'Dropbox' || $element['type'] == 'Radiobutton') {
                        if (isset($_POST['UNI_'.$element['id']])) {
                            $db->addValue('option_id', $_POST['UNI_'.$element['id']]);
                            $db->insert();
                        } 
                    } elseif ($element['type'] == 'Link') {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $link = PHPWS_Text::checkLink(strip_tags($_POST['UNI_'.$element['id']]));
                            if (PHPWS_Text::isValidInput($link, 'url')) {
                                $db->addValue('value', $link);
                                $db->insert();
                            } else {
                                $errors[] = sprintf(dgettext('vlist', 'Check the link address for %s, for formatting errors.'), $element['title']);
                            }
                        }
                    } elseif ($element['type'] == 'GPS') {
                        if (!empty($_POST['UNI_'.$element['gps']])) {
                            $gps = strip_tags($_POST['UNI_'.$element['id']]);
//    NEED A REGEX HERE                        if (PHPWS_Text::isValidInput($gps, 'url')) {
                                $db->addValue('value', $gps);
                                $db->insert();
//                            } else {
//                                $errors[] = sprintf(dgettext('vlist', 'Check the value for %s, for formatting errors.'), $element['title']);
//                            }
                        }
                    } elseif ($element['type'] == 'Email') {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $email = strip_tags($_POST['UNI_'.$element['id']]);
                            if (PHPWS_Text::isValidInput($email, 'email')) {
                                $db->addValue('value', $email);
                                $db->insert();
                            } else {
                                $errors[] = sprintf(dgettext('vlist', 'Check the email address for %s, for formatting errors.'), $element['title']);
                            }
                        }
                    } else {
                        if (!empty($_POST['UNI_'.$element['id']])) {
                            $db->addValue('value', PHPWS_Text::parseInput($_POST['UNI_'.$element['id']]));
                            $db->insert();
                        } 
                    }

                }

            } // end foreach result
        } else { // end if result
            $errors[] = dgettext('vlist', 'Sorry, no custom elements have been setup.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_listing');
            return false;
        } else {
            return true;
        }
    }


    public function postGroup()
    {
        $this->loadGroup();
        
        if (empty($_POST['title'])) {
            $errors[] = dgettext('vlist', 'You must give this group a title.');
        } else {
            $this->group->setTitle($_POST['title']);
        }

        if (isset($_POST['description'])) {
            $this->group->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->group->setImage_id((int)$_POST['image_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }

    }


    public function postSettings()
    {

        if (!empty($_POST['module_title'])) {
            PHPWS_Settings::set('vlist', 'module_title', strip_tags($_POST['module_title']));
        } else {
            $errors[] = dgettext('vlist', 'Please provide a module title.');
        }
        
        isset($_POST['enable_sidebox']) ?
            PHPWS_Settings::set('vlist', 'enable_sidebox', 1) :
            PHPWS_Settings::set('vlist', 'enable_sidebox', 0);

        PHPWS_Settings::set('vlist', 'block_order_by', $_POST['block_order_by']);
        PHPWS_Settings::set('vlist', 'main_order_by', $_POST['main_order_by']);

        isset($_POST['sidebox_homeonly']) ?
            PHPWS_Settings::set('vlist', 'sidebox_homeonly', 1) :
            PHPWS_Settings::set('vlist', 'sidebox_homeonly', 0);

        if (!empty($_POST['sidebox_text'])) {
            PHPWS_Settings::set('vlist', 'sidebox_text', PHPWS_Text::parseInput($_POST['sidebox_text']));
        } else {
            PHPWS_Settings::set('vlist', 'sidebox_text', null);
        }

        isset($_POST['enable_elements']) ?
            PHPWS_Settings::set('vlist', 'enable_elements', 1) :
            PHPWS_Settings::set('vlist', 'enable_elements', 0);

        isset($_POST['enable_groups']) ?
            PHPWS_Settings::set('vlist', 'enable_groups', 1) :
            PHPWS_Settings::set('vlist', 'enable_groups', 0);

        isset($_POST['list_groups']) ?
            PHPWS_Settings::set('vlist', 'list_groups', 1) :
            PHPWS_Settings::set('vlist', 'list_groups', 0);

        if (!empty($_POST['groups_title'])) {
            PHPWS_Settings::set('vlist', 'groups_title', strip_tags($_POST['groups_title']));
        } else {
            $errors[] = dgettext('vlist', 'Please provide a groups title.');
        }
        
        if (!empty($_POST['admin_contact'])) {
            if (PHPWS_Text::isValidInput($_POST['admin_contact'], 'email')) {
                PHPWS_Settings::set('vlist', 'admin_contact', strip_tags(PHPWS_Text::parseInput($_POST['admin_contact'])));
            } else {
                $errors[] = dgettext('vlist', 'Check your e-mail address for formatting errors.');
            }
        } else {
            $errors[] = dgettext('vlist', 'You must provide an e-mail address for the admin.');
        }

        isset($_POST['enable_files']) ?
            PHPWS_Settings::set('vlist', 'enable_files', 1) :
            PHPWS_Settings::set('vlist', 'enable_files', 0);

        if (isset($_POST['enable_images'])) {
            PHPWS_Settings::set('vlist', 'enable_images', 1);
            if ( !empty($_POST['max_width']) ) {
                $max_width = (int)$_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 600 ) {
                    PHPWS_Settings::set('vlist', 'max_width', $max_width);
                }
            }
            if ( !empty($_POST['max_height']) ) {
                $max_height = (int)$_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 600 ) {
                    PHPWS_Settings::set('vlist', 'max_height', $max_height);
                }
            }
        } else {
            PHPWS_Settings::set('vlist', 'enable_images', 0);
        }

        isset($_POST['enable_users']) ?
            PHPWS_Settings::set('vlist', 'enable_users', 1) :
            PHPWS_Settings::set('vlist', 'enable_users', 0);

        isset($_POST['list_users']) ?
            PHPWS_Settings::set('vlist', 'list_users', 1) :
            PHPWS_Settings::set('vlist', 'list_users', 0);

        isset($_POST['user_submissions']) ?
            PHPWS_Settings::set('vlist', 'user_submissions', 1) :
            PHPWS_Settings::set('vlist', 'user_submissions', 0);

        isset($_POST['anon_submissions']) ?
            PHPWS_Settings::set('vlist', 'anon_submissions', 1) :
            PHPWS_Settings::set('vlist', 'anon_submissions', 0);

        isset($_POST['user_files']) ?
            PHPWS_Settings::set('vlist', 'user_files', 1) :
            PHPWS_Settings::set('vlist', 'user_files', 0);

        isset($_POST['anon_files']) ?
            PHPWS_Settings::set('vlist', 'anon_files', 1) :
            PHPWS_Settings::set('vlist', 'anon_files', 0);

        isset($_POST['view_created']) ?
            PHPWS_Settings::set('vlist', 'view_created', 1) :
            PHPWS_Settings::set('vlist', 'view_created', 0);

        isset($_POST['view_updated']) ?
            PHPWS_Settings::set('vlist', 'view_updated', 1) :
            PHPWS_Settings::set('vlist', 'view_updated', 0);

        isset($_POST['list_created']) ?
            PHPWS_Settings::set('vlist', 'list_created', 1) :
            PHPWS_Settings::set('vlist', 'list_created', 0);

        isset($_POST['list_updated']) ?
            PHPWS_Settings::set('vlist', 'list_updated', 1) :
            PHPWS_Settings::set('vlist', 'list_updated', 0);

        isset($_POST['notify_submit']) ?
            PHPWS_Settings::set('vlist', 'notify_submit', 1) :
            PHPWS_Settings::set('vlist', 'notify_submit', 0);

        isset($_POST['notify_edit']) ?
            PHPWS_Settings::set('vlist', 'notify_edit', 1) :
            PHPWS_Settings::set('vlist', 'notify_edit', 0);

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('vlist')) {
                return true;
            } else { 
                return falsel;
            }
        }

    }


    public function alpha_click() 
    {
    
        $alphabet = $this->alphabet();
        $alpha = array();
        $links = array();
        foreach ($alphabet as $alphachar) {
            if (@$_REQUEST['uop'] == "listings") {
                $vars['uop'] = 'listings';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "vlist", $vars) . "\n";
            } elseif (@$_REQUEST['aop'] == "list_expired") {
                $vars['aop'] = 'list_expired';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "vlist", $vars) . "\n";
            } else {
                $vars['uop'] = 'listings';
                $vars['browseLetter'] = $alphachar;
                $alpha[] .= PHPWS_Text::moduleLink($alphachar, "vlist", $vars) . "\n";
            }
        }

        if (@$_REQUEST['uop'] == "listings") {
            $vars['uop'] = 'listings';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'Other'), "vlist",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'All'), "vlist",  array('uop'=>'listings')) . "\n";
        } elseif (@$_REQUEST['aop'] == "list_expired") {
            $vars['aop'] = 'list_expired';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'Other'), "vlist",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'All'), "vlist",  array('aop'=>'list_expired')) . "\n";
        } else {
            $vars['uop'] = 'listings';
            $vars['browseLetter'] = 'Other';
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'Other'), "vlist",  $vars) . "\n";
            $alpha[] .= PHPWS_Text::moduleLink(dgettext('vlist', 'All'), "vlist",  array('uop'=>'listings')) . "\n";
        }

        $links = $this->navLinks();

        $tpl['LIST'] = implode(' | ', $alpha);
        $tpl['LINKS'] = implode(' | ', $links);
        return PHPWS_Template::process($tpl, 'vlist', 'alpha_click.tpl');
    
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
    public function getItemForm($type='group', $match=null, $select_name='group', $multiple=true)
    {

        switch($type) {
            case 'group':
                PHPWS_Core::initModClass('vlist', 'vList_Group.php');
                $db = new PHPWS_DB('vlist_group');
                $db->addOrder('title asc');
                $result = $db->getObjects('vList_Group');
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


    public function getItemSelect($type='group', $match=null, $select_name='group', $multiple=true, $count=true)
    {

        switch($type) {
            case 'group':
                PHPWS_Core::initModClass('vlist', 'vList_Group.php');
                $db = new PHPWS_DB('vlist_group');
                $db->addOrder('title asc');
                $result = $db->getObjects('vList_Group');
                break;
        }

        if ($result) {
            foreach ($result as $item) {
                if ($count) {
                    $db = new PHPWS_DB('vlist_'.$type.'_items');
                    $db->addWhere($type.'_id', $item->id);
                    if (!Current_User::isUnrestricted('vlist')) {
                        $db->addColumn('vlist_'.$type.'_items.*');
                        $db->addColumn('vlist_listing.id');
                        $db->addWhere('vlist_listing.id', 'vlist_'.$type.'_items.listing_id');
                        $db->addWhere('vlist_listing.active', 1);
                        $db->addGroupBy('vlist_listing.id'); 
                    }
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
            return dgettext('vlist', 'No choices configured.');
        }
        
    }


    public function getExtrasSelect($id=null, $match=null, $select_name=null, $multiple=true, $count=true)
    {

        $select_name = 'UNI_' . $id;
        $db = new PHPWS_DB('vlist_element_option');
        $db->addWhere('element_id', $id);
        $db->addOrder('sort asc');
        $result = $db->select();

        if ($result) {
            foreach ($result as $option) {
                if ($count) {
                    $db = new PHPWS_DB('vlist_element_items');
                    $db->addWhere('option_id', $option['id']);
                    if (!Current_User::isUnrestricted('vlist')) {
                        $db->addColumn('vlist_element_items.*');
                        $db->addColumn('vlist_listing.id');
                        $db->addWhere('vlist_listing.id', 'vlist_element_items.listing_id');
                        $db->addWhere('vlist_listing.active', 1);
                        $db->addGroupBy('vlist_listing.id'); 
                    }
                    $qty = $db->count();
                    $options[$option['id']] = $option['label'] . ' ('.$qty.')';
                } else {
                    $options[$option['id']] = $option['label'];
                }
            }
        }

        if ($options) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $options);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $options);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('vlist', 'No choices configured.');
        }
        
    }


    public function navLinks()
    {

        $links[] = PHPWS_Text::moduleLink(dgettext('vlist', 'All Listings'), 'vlist', array('uop'=>'listings'));
        if (PHPWS_Settings::get('vlist', 'enable_groups')) {
            $db = new PHPWS_DB('vlist_group');
            $groups = $db->count();
            if ($groups > 0) {
                $links[] = PHPWS_Text::moduleLink(PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'groups_title')), "vlist",  array('uop'=>'groups'));
            }
        } else {
            $groups = null;
        }
        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $db = new PHPWS_DB('vlist_element');
            $db->addWhere('active', 1);
            $elements = $db->count();
        } else {
            $elements = null;
        }

        if ($groups > 0 || $elements > 0) {
            $links[] = PHPWS_Text::moduleLink(dgettext('vlist', 'Advanced'), "vlist",  array('uop'=>'advanced'));
        }
        if (Current_User::allow('vlist', 'edit_listing')) {
            $links[] = PHPWS_Text::secureLink(dgettext('vlist', 'Add Listing'), 'vlist', array('aop'=>'new_listing'));
        } elseif (PHPWS_Settings::get('vlist', 'anon_files') || (PHPWS_Settings::get('vlist', 'user_files') && $_SESSION['User']->username != '')) {
            $links[] = PHPWS_Text::moduleLink(dgettext('vlist', 'Submit a listing'), 'vlist', array('uop'=>'submit_listing'));
        }
        if (Current_User::allow('vlist', 'settings', null, null, true) && !isset($_REQUEST['aop'])){
            $links[] = PHPWS_Text::moduleLink(dgettext('vlist', 'Settings'), "vlist",  array('aop'=>'menu', 'tab'=>'settings'));
        }
        
        return $links;
    }



}
?>