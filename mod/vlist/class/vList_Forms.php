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

class vList_Forms {
    public $vlist = null;

    public function get($type)
    {
        switch ($type) {

        case 'new_listing':
        case 'edit_listing':
            if (empty($this->vlist->listing)) {
                $this->vlist->loadListing();
            }
            $this->editListing();
            break;

        case 'new_group':
        case 'edit_group':
            if (empty($this->vlist->group)) {
                $this->vlist->loadGroup();
            }
            $this->vlist->panel->setCurrentTab('groups');
            $this->editGroup();
            break;

        case 'listings':
            $this->vlist->panel->setCurrentTab('listings');
            $this->listListings(1);
            break;

        case 'approvals':
            $this->vlist->panel->setCurrentTab('approvals');
            $this->listListings(0);
            break;

        case 'groups':
            $this->vlist->panel->setCurrentTab('groups');
            $this->listGroups();
            break;

        case 'elements':
            $this->vlist->panel->setCurrentTab('elements');
            $this->listElements();
            break;

        case 'settings':
            $this->vlist->panel->setCurrentTab('settings');
            $this->editSettings();
            break;

        case 'info':
            $this->vlist->panel->setCurrentTab('info');
            $this->showInfo();
            break;

        }

    }


    public function settingsPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=vlist&aop=menu';

        if (Current_User::allow('vlist', 'settings', null, null, true)){
            $tags['settings'] = array('title'=>dgettext('vlist', 'Settings'),
                                  'link'=>$link);
            if (PHPWS_Settings::get('vlist', 'enable_elements')) {
                $tags['elements'] = array('title'=>dgettext('vlist', 'Extra Fields'),
                                      'link'=>$link);
            }
            if (PHPWS_Settings::get('vlist', 'enable_groups')) {
                $tags['groups'] = array('title'=>PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'groups_title')),
                                      'link'=>$link);
            }
        }

        $panel = new PHPWS_Panel('vlist-settings-panel');
        $panel->quickSetTabs($tags);
        $panel->setModule('vlist');
        return $panel;
    }


    public function listListings($approved=null, $group=null, $owner=null)
    {
        if (Current_User::allow('vlist', 'edit_listing') && isset($_REQUEST['uop'])) {
            $link[] = PHPWS_Text::secureLink(dgettext('vlist', 'Add new listing'), 'vlist', array('aop'=>'new_listing'));
            MiniAdmin::add('vlist', $link);
        }

        PHPWS_Core::initModClass('vlist', 'vList_Listing.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vlist_listing', 'vList_Listing');
        $pager->setModule('vlist');
        $pager->db->addColumn('vlist_listing.*');

        /* approved/active yes/no */
        if (isset($approved)) {
            $pager->addWhere('active', $approved);
        }

        $ptags['COLSPAN'] = 2;

        $pager->addSortHeader('title', 'Title');
        if (PHPWS_Settings::get('vlist', 'enable_users') && PHPWS_Settings::get('vlist', 'list_users')) {
            $pager->addSortHeader('owner_id', 'Listed by');
            $ptags['COLSPAN'] = $ptags['COLSPAN'] + 1;
        } else {
            $ptags['OWNER_ID_SORT'] = null;
        }

        if (PHPWS_Settings::get('vlist', 'list_created')) {
            $pager->addSortHeader('created', 'Created');
            $ptags['COLSPAN'] = $ptags['COLSPAN'] + 1;
        } else {
            $ptags['CREATED_SORT'] = null;
        }

        if (PHPWS_Settings::get('vlist', 'list_updated')) {
            $pager->addSortHeader('updated', 'Updated');
            $ptags['COLSPAN'] = $ptags['COLSPAN'] + 1;
        } else {
            $ptags['UPDATED_SORT'] = null;
        }

        if (!isset($_REQUEST['aop'])) {        
            $ptags['ALPHA_CLICK'] = $this->vlist->alpha_click();
        }

        if (!Current_User::isUnrestricted('vlist')) {
            $pager->addWhere('active', 1);
        }

        /* unset the filters if a search is being done */
        /* need to add the extras here somehow maybe */
        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            unset($_REQUEST['browseLetter']);
//            unset($_REQUEST['groups']);
//            unset($_REQUEST['owner']);
        }

        /* if the alpha click list is being used */
        if (isset($_REQUEST['browseLetter'])) {
            if ($_REQUEST['browseLetter'] == 'Other') {
                $pager->db->addWhere('title', '^[^a-z]', 'REGEXP');
            } else {
                $pager->db->addWhere('title', $_REQUEST['browseLetter'].'%', 'LIKE');
            }
        } 
        
        /* if it's a list by group */
        if ($group) {
            $_REQUEST['groups'] = $group;
            PHPWS_Core::initModClass('vlist', 'vList_Group.php');
            $item = new vList_Group($group);
            Layout::addPageTitle($item->getTitle());
            $ptags['ITEM_TITLE'] = $item->getTitle(true);
            $ptags['ITEM_DESCRIPTION'] = PHPWS_Text::parseTag($item->getDescription(true));
            $ptags['ITEM_IMAGE'] = $item->getFile();
            if ($item->getFile()) {
                $ptags['ITEM_IMAGE'] = $item->getFile();
                $ptags['ITEM_CLEAR_FLOAT'] = '<br style="clear: right;" />';
            }
        }

        /* if it's a list by owner */
        if ($owner) {
            if (PHPWS_Settings::get('vlist', 'show_users')) {
                $_REQUEST['owner'] = $owner;
                if (PHPWS_Core::moduleExists('rolodex')) {
                    PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
                    $user = new Rolodex_Member($owner);
                    if ($user) {
                        Layout::addPageTitle($user->getDisplay_name());
                        $ptags['ITEM_TITLE'] = $user->getDisplay_name();
                        $ptags['ITEM_DESCRIPTION'] = PHPWS_Text::parseTag($user->getDescription(true));
                        $ptags['ITEM_CONTACT_LINK'] = sprintf(dgettext('vlist', 'Contact %s'), $user->getDisplay_email(true));
                        $ptags['ITEM_VIEW_LINK'] = sprintf(dgettext('vlist', 'See profile for %s'), $user->viewLink());
                        if ($user->getImage()) {
                            $ptags['ITEM_IMAGE'] = $user->getImage(true);
                            $ptags['ITEM_CLEAR_FLOAT'] = '<br style="clear: right;" />';
                        }
                    }
                } else {
                    $user = new PHPWS_User($owner);
                    Layout::addPageTitle($user->getDisplayName());
                    $ptags['ITEM_TITLE'] = $user->getDisplayName();
                }
            }
        }

        /* search by group */ 
        if (isset($_REQUEST['groups'])) {
            $pager->db->addColumn('vlist_group_items.*');
            $pager->db->addWhere('vlist_listing.id', 'vlist_group_items.listing_id');
            $pager->db->addWhere('vlist_group_items.group_id', $_REQUEST['groups']);
            $pager->db->addGroupBy('vlist_listing.id'); // was causing probs, incomplete results
        }

        /* search by owner */ 
        if (isset($_REQUEST['owner'])) {
            $pager->db->addWhere('owner_id', $_REQUEST['owner']);
        }


        /* search by extras start */
        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $db = new PHPWS_DB('vlist_element');
            $db->addWhere('active', 1);
            if (!Current_User::allow('vlist')) {
                $db->addWhere('private', 0);
            }
            $db->addOrder('sort asc');
            $result = $db->select();

            if ($result) {
                foreach ($result as $element) {
                    $id = $element['id'];
                    $type = $element['type'];
                    /* add flagged extras to list as columns */
                    if ($element['list']) {
                        $ptags['EXTRA_TITLES'][]['EXT_TITLE'] = $element['title'];
                        $ptags['COLSPAN'] = $ptags['COLSPAN'] + 1;
                    }
                    /* unset the filters if a search is being done */
                    /* doesn't seem to be working not sure why */
//                    if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
                    if (isset($_REQUEST['pager_c_search']) && !empty($_REQUEST['pager_c_search'])) {
                        if (isset($_REQUEST['UNI_' . $id])) {
                            unset($_REQUEST['UNI_' . $id . '%5B' . $_REQUEST['UNI_' . $id] . '%5D']);
                        }
                    }
                    if ($type == 'Checkbox' || $type == 'Dropbox' || $type == 'Multiselect' || $type == 'Radiobutton') {
//print_r($_REQUEST['UNI_' . $id]);
                        
                        if (isset($_REQUEST['UNI_' . $id])) {
                            foreach ($_REQUEST['UNI_' . $id] as $option) {
                                $all_options[$id][] = $option;
                            }
                        }

                    } else {
                        if (isset($_REQUEST['pager_c_search']) && $_REQUEST['pager_c_search'] !== '') {
                            $pager->db->addColumn('vlist_element_items.*');
                            $pager->db->addWhere('vlist_listing.id', 'vlist_element_items.listing_id');
                            $pager->db->addWhere('vlist_element_items.value', '%' . $_REQUEST['pager_c_search'] . '%', 'like', 'and');
                            $pager->setSearch('vlist_element_items.value', 'vlist_listing.title', 'vlist_listing.description');
                        }
                    }
                }

                if (!empty($all_options)) {

                    $db = new PHPWS_DB('vlist_element_items');
                    $db->addColumn('listing_id');
                    $total_options = 0;

                    foreach ($all_options as $optin) {
                        $total_options++;
                        foreach($optin as $opt) {
                            $bar[] = $opt;
                        }
                    }
                    $db->addWhere('option_id', $bar, 'in', 'and');
                    $foo = $db->select('col');

                    if (!empty($foo)) {
                        if ($total_options > 1) {
                            $cfoo = array_count_values($foo);
                            $sfoo = array_keys($cfoo, $total_options);
                        } else {
                            $sfoo = & $foo;
                        }
                        if (empty($sfoo)) {
                            $pager->db->addWhere('vlist_listing.id', '0', '<');
                        } else {
                            $pager->db->addWhere('vlist_listing.id', $sfoo, 'in');
                        }
                    }
                }

            } 
        }

        /* set the default sorts */
        if (PHPWS_Settings::get('vlist', 'main_order_by') == 3) {
            $pager->db->addOrder('rand');
        } elseif (PHPWS_Settings::get('vlist', 'main_order_by') == 2) {
            $pager->setOrder('updated', 'desc', true);
        } elseif (PHPWS_Settings::get('vlist', 'main_order_by') == 1) {
            $pager->setOrder('created', 'desc', true);
        } else {
            $pager->setOrder('title', 'asc', true);
        }

        /* not sure if the next bit is used */
        /* actually it is, but not really accurate anymore as not all filters are being cleared */
        if (isset($ptags['ITEM_TITLE'])) {
            $ptags['CLEAR_FILTERS'] = sprintf(dgettext('vlist', 'Search within %s.'), $ptags['ITEM_TITLE']);
        } else {
            $ptags['CLEAR_FILTERS'] = dgettext('vlist', 'Clears filters and searches all records.');
        }

        $pager->setTemplate('list_listings.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0' && Current_User::allow('vlist', 'edit_listing')) {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'new_listing';
            if (isset($approved) && $approved == 1) {
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vlist', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('vlist', 'Settings'), 'vlist', $vars),  PHPWS_Text::secureLink(dgettext('vlist', 'New Listing'), 'vlist', $vars2));
            }
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('vlist_listing.title', 'vlist_listing.description');
//        $pager->cacheQueries();
// $pager->db->setTestMode();
// print_r($ptags); //exit();
        $this->vlist->content = $pager->get();

        /* set the list/page title */
        if (isset($approved) && $approved == 0) {
            $this->vlist->title = sprintf(dgettext('vlist', 'Unapproved %s Listings'), PHPWS_Settings::get('vlist', 'module_title'));
        } else {
            $this->vlist->title = sprintf(dgettext('vlist', '%s Listings'), PHPWS_Settings::get('vlist', 'module_title'));
            if (isset($ptags['ITEM_TITLE'])) {
                $this->vlist->title .= sprintf(dgettext('vlist', ' - %s'), $ptags['ITEM_TITLE']);
            }
        }

    }


    public function listGroups()
    {
        $ptags['TITLE_HEADER'] = dgettext('vlist', 'Title');
        $ptags['DESCRIPTION_HEADER'] = dgettext('vlist', 'Description');
        if (!isset($_REQUEST['aop'])) {        
            $ptags['ALPHA_CLICK'] = $this->vlist->alpha_click();
        }

        PHPWS_Core::initModClass('vlist', 'vList_Group.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vlist_group', 'vList_Group');
        $pager->setModule('vlist');
        $pager->setDefaultOrder('title', 'asc', true);
        $pager->setTemplate('list_groups.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            if (Current_User::allow('vlist', 'settings', null, null, true)) {
                $vars['aop']  = 'menu';
                $vars['tab']  = 'settings';
                $vars2['aop']  = 'new_group';
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vlist', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('vlist', 'Settings'), 'vlist', $vars),  PHPWS_Text::secureLink(dgettext('vlist', 'New Group'), 'vlist', $vars2));
            } else {
                $ptags['EMPTY_MESSAGE'] = dgettext('vlist', 'Sorry, no groups are available at this time.');
            }
        }
        if (Current_User::allow('vlist', 'settings', null, null, true)) {
            $vars['aop']  = 'new_group';
            $ptags['ADD_LINK'] = PHPWS_Text::secureLink(dgettext('vlist', 'Add Group'), 'vlist', $vars);
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

        $this->vlist->content = $pager->get();
        $this->vlist->title = sprintf('%s %s', PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'module_title')), PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'groups_title')));
    }


    public function listElements()
    {
        $ptags['TITLE_HEADER'] = dgettext('vlist', 'Title');
        $ptags['TYPE_HEADER'] = dgettext('vlist', 'Type');
        $ptags['SORT_HEADER'] = dgettext('vlist', 'Sort');
        $ptags['LIST_HEADER'] = dgettext('vlist', 'List');
        $ptags['SEARCH_HEADER'] = dgettext('vlist', 'Search');
        $ptags['PRIVATE_HEADER'] = dgettext('vlist', 'Private');
        $ptags['ADD_FORM'] = $this->addElement();

        PHPWS_Core::initModClass('vlist', 'UNI_Element.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vlist_element', 'UNI_Element');
        $pager->setModule('vlist');
        $pager->setOrder('sort', 'asc', true);
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_elements.tpl');
        $pager->addRowTags('rowTag');
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title');

        $this->vlist->content = $pager->get();
        $this->vlist->title = sprintf(dgettext('vlist', '%s Elements'), PHPWS_Settings::get('vlist', 'module_title'));
    }


    public function editListing()
    {
        $form = new PHPWS_Form('vlist_listing');
        $listing = & $this->vlist->listing;

        $form->addHidden('module', 'vlist');
        if ($listing->id) {
            $form->addHidden('aop', 'post_listing');
            $form->addHidden('id', $listing->id);
            $form->addSubmit(dgettext('vlist', 'Update'));
            $this->vlist->title = sprintf(dgettext('vlist', 'Update %s listing'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'module_title')));
        } elseif (isset($_REQUEST['uop']) && $_REQUEST['uop'] == 'submit_listing') {
            $form->addHidden('uop', 'post_listing');
            $form->addSubmit(dgettext('vlist', 'Submit'));
            $this->vlist->title = sprintf(dgettext('vlist', 'Submit %s listing'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'module_title')));
        } else {
            $form->addHidden('aop', 'post_listing');
            $form->addSubmit(dgettext('vlist', 'Create'));
            $this->vlist->title = sprintf(dgettext('vlist', 'Create %s listing'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'module_title')));
        }

        if (PHPWS_Settings::get('vlist', 'enable_users') && Current_User::allow('vlist', 'edit_listing', null, null, true)) {
            $db = new PHPWS_DB('users');
            $db->addColumn('id');
            $db->addColumn('username');
            $db->addColumn('display_name');
            $db->addOrder('display_name');
            $result = $db->getObjects('PHPWS_User');
            if ($result) {
                $choices[0] = dgettext('vlist', 'Anonymous');
                foreach ($result as $user) {
                    $choices[$user->id] = $user->display_name;
                }
                $form->addSelect('owner_id', $choices);
                $form->setMatch('owner_id', $listing->owner_id);
                $form->setLabel('owner_id', dgettext('vlist', 'Listing owner'));
            }
        } else {
            if (!$listing->id) {
                $form->addHidden('owner_id', Current_User::getId());
            }
        }

        $form->addText('title', $listing->getTitle());
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('vlist', 'Title'));

        if (isset($_REQUEST['uop']) && $_REQUEST['uop'] == 'submit_listing') {
//            $form->addHidden('active', null);
        } else {
            $form->addCheckbox('active', 1);
            $form->setMatch('active', $listing->active);
            $form->setLabel('active', dgettext('vlist', 'Active'));
        }

        $form->addTextArea('description', $listing->getDescription());
        $form->useEditor('description', true, true, 0, 0, 'fckeditor');
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setRequired('description');
        $form->setLabel('description', dgettext('vlist', 'Description'));

        if (PHPWS_Settings::get('vlist', 'enable_images')) {
            if (PHPWS_Settings::get('vlist', 'anon_files') || (PHPWS_Settings::get('vlist', 'user_files') && $_SESSION['User']->username != '') || Current_User::allow('vlist', 'edit_listing')) {
                PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
                $manager = Cabinet::fileManager('image_id', $listing->image_id);
                $manager->imageOnly();
                $manager->maxImageWidth(PHPWS_Settings::get('vlist', 'max_width'));
                $manager->maxImageHeight(PHPWS_Settings::get('vlist', 'max_height'));
                if ($manager) {
                    $form->addTplTag('IMAGE_MANAGER', $manager->get());
                }
            }
        }

        if (PHPWS_Settings::get('vlist', 'enable_files')) {
            if (PHPWS_Settings::get('vlist', 'anon_files') || (PHPWS_Settings::get('vlist', 'user_files') && $_SESSION['User']->username != '') || Current_User::allow('vlist', 'edit_listing')) {
                PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
                $manager = Cabinet::fileManager('file_id', $listing->file_id);
                $manager->documentOnly();
                if ($manager) {
                    $form->addTplTag('FILE_MANAGER', $manager->get());
                }
            }
        }

        $tpl = $form->getTemplate();

        if (PHPWS_Settings::get('vlist', 'enable_groups')) {
            $tpl['GROUPS'] = $this->vlist->getItemForm('group', $match=$listing->get_groups(), $select_name='groups', $multiple=true);
            $tpl['GROUPS_LABEL'] = dgettext('vlist', 'Group');
        }

        $tpl['DETAILS_LABEL'] = dgettext('vlist', 'Details');
        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $tpl['EXTRAS_LABEL'] = dgettext('vlist', 'Extra');
            $tpl['EXTRAS'] = $this->getExtrasForm();
        }

        $this->vlist->content = PHPWS_Template::process($tpl, 'vlist', 'edit_listing.tpl');
    }


    public function editGroup()
    {
        $form = new PHPWS_Form('vlist_group');
        $group = & $this->vlist->group;

        $form->addHidden('module', 'vlist');
        $form->addHidden('aop', 'post_group');

        if ($group->id) {
            $form->addHidden('id', $group->id);
            $form->addSubmit(dgettext('vlist', 'Update'));
            $this->vlist->title = sprintf(dgettext('vlist', 'Update %s group'), PHPWS_Settings::get('vlist', 'module_title'));
        } else {
            $form->addSubmit(dgettext('vlist', 'Create'));
            $this->vlist->title = sprintf(dgettext('vlist', 'Create %s group'), PHPWS_Settings::get('vlist', 'module_title'));
        }

        $form->addText('title', $group->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('vlist', 'Title'));
        $form->setRequired('title');

        $form->addTextArea('description', $group->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('vlist', 'Description'));

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $group->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(PHPWS_Settings::get('vlist', 'max_width'));
        $manager->maxImageHeight(PHPWS_Settings::get('vlist', 'max_height'));

        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('vlist', 'Details');

        $this->vlist->content = PHPWS_Template::process($tpl, 'vlist', 'edit_group.tpl');

    }


    public function editSettings()
    {

        $form = new PHPWS_Form('vlist_settings');
        $form->addHidden('module', 'vlist');
        $form->addHidden('aop', 'post_settings');

        $form->addText('module_title', PHPWS_Settings::get('vlist', 'module_title'));
        $form->setSize('module_title', 30);
        $form->setRequired('module_title');
        $form->setLabel('module_title', dgettext('vlist', 'The display title for this module, eg. vList, Properties, Listings, etc.'));

        $form->addCheckbox('enable_sidebox', 1);
        $form->setMatch('enable_sidebox', PHPWS_Settings::get('vlist', 'enable_sidebox'));
        $form->setLabel('enable_sidebox', dgettext('vlist', 'Enable vlist sidebox'));

        $form->addCheckbox('sidebox_homeonly', 1);
        $form->setMatch('sidebox_homeonly', PHPWS_Settings::get('vlist', 'sidebox_homeonly'));
        $form->setLabel('sidebox_homeonly', dgettext('vlist', 'Show sidebox on home page only'));

        $form->addTextArea('sidebox_text', PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'sidebox_text')));
        $form->setRows('sidebox_text', '4');
        $form->setCols('sidebox_text', '40');
        $form->setLabel('sidebox_text', dgettext('vlist', 'Sidebox text'));

        $form->addRadio('block_order_by', array(0, 1));
        $form->setLabel('block_order_by', array(dgettext('vlist', 'Most recent'), dgettext('vlist', 'Random')));
        $form->setMatch('block_order_by', PHPWS_Settings::get('vlist', 'block_order_by'));
    
        $form->addRadio('main_order_by', array(0, 1, 2, 3));
        $form->setLabel('main_order_by', array(dgettext('vlist', 'Title'), dgettext('vlist', 'Created'), dgettext('vlist', 'Updated'), dgettext('vlist', 'Random')));
        $form->setMatch('main_order_by', PHPWS_Settings::get('vlist', 'main_order_by'));
    
        $form->addCheckbox('enable_elements', 1);
        $form->setMatch('enable_elements', PHPWS_Settings::get('vlist', 'enable_elements'));
        $form->setLabel('enable_elements', dgettext('vlist', 'Enable extra fields'));

        $form->addCheckbox('enable_groups', 1);
        $form->setMatch('enable_groups', PHPWS_Settings::get('vlist', 'enable_groups'));
        $form->setLabel('enable_groups', dgettext('vlist', 'Enable groups'));

        $form->addText('groups_title', PHPWS_Settings::get('vlist', 'groups_title'));
        $form->setSize('groups_title', 30);
        $form->setRequired('groups_title');
        $form->setLabel('groups_title', dgettext('vlist', 'The display title for groups, eg. Groups, Regions, Categories, etc.'));

        $form->addCheckbox('list_groups', 1);
        $form->setMatch('list_groups', PHPWS_Settings::get('vlist', 'list_groups'));
        $form->setLabel('list_groups', dgettext('vlist', 'Include groups in list view'));

        $form->addText('admin_contact', PHPWS_Settings::get('vlist', 'admin_contact'));
        $form->setSize('admin_contact', 30);
        $form->setRequired('admin_contact');
        $form->setLabel('admin_contact', dgettext('vlist', 'Admin email address'));

        $form->addCheckbox('enable_files', 1);
        $form->setMatch('enable_files', PHPWS_Settings::get('vlist', 'enable_files'));
        $form->setLabel('enable_files', dgettext('vlist', 'Enable files on listings'));

        $form->addCheckbox('enable_images', 1);
        $form->setMatch('enable_images', PHPWS_Settings::get('vlist', 'enable_images'));
        $form->setLabel('enable_images', dgettext('vlist', 'Enable images on listings'));

        $form->addTextField('max_width', PHPWS_Settings::get('vlist', 'max_width'));
        $form->setLabel('max_width', dgettext('vlist', 'Maximum image width (50-600)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', PHPWS_Settings::get('vlist', 'max_height'));
        $form->setLabel('max_height', dgettext('vlist', 'Maximum image height (50-600)'));
        $form->setSize('max_height', 4,4);

        $form->addCheckbox('enable_users', 1);
        $form->setMatch('enable_users', PHPWS_Settings::get('vlist', 'enable_users'));
        $form->setLabel('enable_users', dgettext('vlist', 'Enable user profiles (uses Rolodex data if available)'));

        $form->addCheckbox('show_users', 1);
        $form->setMatch('show_users', PHPWS_Settings::get('vlist', 'show_users'));
        $form->setLabel('show_users', dgettext('vlist', 'Show user details'));

        $form->addCheckbox('list_users', 1);
        $form->setMatch('list_users', PHPWS_Settings::get('vlist', 'list_users'));
        $form->setLabel('list_users', dgettext('vlist', 'Display users in list view'));

        $form->addCheckbox('user_submissions', 1);
        $form->setMatch('user_submissions', PHPWS_Settings::get('vlist', 'user_submissions'));
        $form->setLabel('user_submissions', dgettext('vlist', 'Enable user submissions'));

        $form->addCheckbox('anon_submissions', 1);
        $form->setMatch('anon_submissions', PHPWS_Settings::get('vlist', 'anon_submissions'));
        $form->setLabel('anon_submissions', dgettext('vlist', 'Enable anonymous submissions'));

        $form->addCheckbox('user_files', 1);
        $form->setMatch('user_files', PHPWS_Settings::get('vlist', 'user_files'));
        $form->setLabel('user_files', dgettext('vlist', 'Allow user img/file access'));

        $form->addCheckbox('anon_files', 1);
        $form->setMatch('anon_files', PHPWS_Settings::get('vlist', 'anon_files'));
        $form->setLabel('anon_files', dgettext('vlist', 'Allow anonymous img/file access'));

        $form->addCheckbox('view_created', 1);
        $form->setMatch('view_created', PHPWS_Settings::get('vlist', 'view_created'));
        $form->setLabel('view_created', dgettext('vlist', 'Display created date on details view'));

        $form->addCheckbox('view_updated', 1);
        $form->setMatch('view_updated', PHPWS_Settings::get('vlist', 'view_updated'));
        $form->setLabel('view_updated', dgettext('vlist', 'Display updated date on details view'));

        $form->addCheckbox('list_created', 1);
        $form->setMatch('list_created', PHPWS_Settings::get('vlist', 'list_created'));
        $form->setLabel('list_created', dgettext('vlist', 'Display created date in list view'));

        $form->addCheckbox('list_updated', 1);
        $form->setMatch('list_updated', PHPWS_Settings::get('vlist', 'list_updated'));
        $form->setLabel('list_updated', dgettext('vlist', 'Display updated date in list view'));

        $form->addCheckbox('notify_submit', 1);
        $form->setMatch('notify_submit', PHPWS_Settings::get('vlist', 'notify_submit'));
        $form->setLabel('notify_submit', dgettext('vlist', 'Send admin notice of new submissions'));

        $form->addCheckbox('notify_edit', 1);
        $form->setMatch('notify_edit', PHPWS_Settings::get('vlist', 'notify_edit'));
        $form->setLabel('notify_edit', dgettext('vlist', 'Send admin notice of edits'));

        $form->addSubmit('save', dgettext('vlist', 'Save settings'));
        
        $tpl = $form->getTemplate();
        $tpl['GENERAL_LABEL'] = dgettext('vlist', 'General Settings');
        $tpl['MAIN_SORT_LABEL'] = dgettext('vlist', 'Default sort order for listings');
        $tpl['LISTING_LABEL'] = dgettext('vlist', 'Listing Template');
        $tpl['SUBMISSION_LABEL'] = dgettext('vlist', 'Submission Settings');

        $this->vlist->title = dgettext('vlist', 'Settings');
        $this->vlist->content = PHPWS_Template::process($tpl, 'vlist', 'edit_settings.tpl');
    }


    public function addElement()
    {
        if (isset($_REQUEST['type'])) {
            $match = $_REQUEST['type'];
        } else {
            $match = null;
        }
        
        $types['Dropbox'] = dgettext('vlist', 'Dropbox');
        $types['Textfield'] = dgettext('vlist', 'Textfield');
        $types['Textarea'] = dgettext('vlist', 'Textarea');
        $types['Multiselect'] = dgettext('vlist', 'Multiple Select');
        $types['Radiobutton'] = dgettext('vlist', 'Radio Button');
        $types['Checkbox'] = dgettext('vlist', 'Checkbox');
        $types['0'] = dgettext('vlist', '----');
        $types['Link'] = dgettext('vlist', 'Link');
        $types['GPS'] = dgettext('vlist', 'GPS');
        $types['Email'] = dgettext('vlist', 'Email');
        $types['GMap'] = dgettext('vlist', 'Google Map');
        $types['00'] = dgettext('vlist', '----');
        $types['Div'] = dgettext('vlist', 'Divider');

        $form = new PHPWS_Form;
        $form->addHidden('module', 'vlist');
        $form->addHidden('aop', 'new_element');
        $form->addSelect('type', $types);
        $form->setMatch('type', $match);
        $form->addSubmit('Add', dgettext('vlist', 'Add'));
        $tpl = $form->getTemplate();
        return  PHPWS_Template::process($tpl, 'vlist', 'elements/add_element.tpl');

    }


    public function getExtrasForm() 
    {
        $listing = & $this->vlist->listing;
        $form = null;
        $db = new PHPWS_DB('vlist_element');
        $db->addWhere('active', 1);
        $db->addOrder('sort asc');
        $result = $db->select();
        if ($result) {
            foreach ($result as $element) {
                $id = $element['id'];
                $type = $element['type'];
                if ($type == 'Link' || $type == 'GPS' || $type == 'Email' || $type == 'GMap') {
                    $type = 'Textfield';
                } else {
                    $type = $type;
                }
                $db = new PHPWS_DB('vlist_element_items');
                $db->addWhere('element_id', $id);
                $db->addWhere('listing_id', $listing->id);
                $result = $db->select();
//print_r($result); exit;
                if ($result) {
                    $match = array();
                    if ($type == 'Checkbox' || $type == 'Multiselect') {
                        foreach ($result as $option) {
                            $match[] = $option['option_id'];
                        }
                    } elseif ($type == 'Dropbox' || $type == 'Radiobutton') {
                        foreach ($result as $option) {
                            $match = $option['option_id'];
                        }
                    } else {
                        foreach ($result as $option) {
                            $match = $option['value'];
                        }
                    }
                } else { 
                    $match = null;
                }
                $class = 'UNI_' . $type;
                PHPWS_Core::initModClass('vlist', 'elements/' . $class . '.php');
                $field = new $class($id);
                $tpl['FIELD'] = $field->view($match);
                $form .= PHPWS_Template::processTemplate($tpl, 'vlist', 'listing_extras_form.tpl');
            }
        } else {
            $form = dgettext('vlist', 'Sorry, no custom elements have been setup.');
        }
        return $form;
    }


    public function advSearchForm()
    {
        $form = new PHPWS_Form('vlist_adv_search');

        $form->setMethod('get');
        $form->addHidden('module', 'vlist');
        $form->addHidden('uop', 'adv_search');
        $form->addText('pager_c_search');

        $form->addSubmit(dgettext('vlist', 'Go'));
        $tpl = $form->getTemplate();

        $tpl['ALPHA_CLICK'] = $this->vlist->alpha_click();

        if (PHPWS_Settings::get('vlist', 'enable_groups')) {
            $tpl['GROUP_SELECT'] = $this->vlist->getItemSelect('group', null, 'groups');
            $tpl['GROUP_LABEL'] = PHPWS_Settings::get('vlist', 'groups_title');
        }
        
        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $tpl['EXTRAS'] = null;
            $db = new PHPWS_DB('vlist_element');
            $db->addWhere('active', 1);
            $db->addWhere('search', 1);
            if (!Current_User::allow('vlist')) {
                $db->addWhere('private', 0);
            }
            $db->addOrder('sort asc');
            $result = $db->select();
            if ($result) {
                foreach ($result as $element) {
                    $id = $element['id'];
                    $type = $element['type'];
                    if ($type == 'Checkbox' || $type == 'Multiselect' || $type == 'Dropbox' || $type == 'Radiobutton') {
                        $element['LABEL'] = PHPWS_Text::parseOutput($element['title']);
                        $element['FIELD'] = $this->vlist->getExtrasSelect($id);
                    } else {
                        $element['LABEL'] = null;
                        $element['FIELD'] = null;
                    }
                    $tpl['EXTRAS'] .= PHPWS_Template::processTemplate($element, 'vlist', 'adv_search_extras.tpl');
                }
            } else {
                $tpl['EXTRAS'] = dgettext('vlist', 'Sorry, no custom elements have been setup.');
            }
        }
        
        $tpl['CRITERIA_LABEL'] = dgettext('vlist', 'Criteria');
        $tpl['SEARCH_LABEL'] = dgettext('vlist', 'Search');
        $tpl['TIP_SELECT'] = dgettext('vlist', 'Select one or more of the available options below to filter a list of members. It is possible to get too specific. If your search returns an empty list, try selecting fewer criteria.');
        $tpl['TIP_MULTI'] = dgettext('vlist', 'To select more than one itme from any given list, click on the first item then hold your control key and click on the next. Use shift-click to select a range of items. Mac users, use command-click and shift-click.');

        $this->vlist->title = sprintf(dgettext('vlist', '%s Advanced Search'), PHPWS_Settings::get('vlist', 'module_title'));
        $this->vlist->content = PHPWS_Template::process($tpl, 'vlist', 'adv_search_form.tpl');

    }


    public function showInfo()
    {
        
        $filename = 'mod/vlist/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('vlist', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('vlist', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('vlist', 'If you would like to help out with the ongoing development of vlist, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=vList%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->vlist->title = dgettext('vlist', 'Read me');
        $this->vlist->content = PHPWS_Template::process($tpl, 'vlist', 'info.tpl');
    }



}

?>