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
class Rolodex_Forms {

    public $rolodex = null;

    public function get($type)
    {
        switch ($type) {

            case 'new':
                $this->selectUser();
                break;

            case 'submit':
                // not used at moment, may use from uop menu
                $this->selectUser();
                break;

            case 'edit_member':
                if (empty($this->rolodex->member)) {
//                    $this->rolodex->loadMember($_REQUEST['user_id']); // I don't think the id in here matters but am unsure?
                    $this->rolodex->loadMember(); // I don't think the id in here matters but am unsure?
                }

                if (!isset($this->rolodex->member->user_id)) {
                    $this->rolodex->member->user_id = $_REQUEST['user_id'];
                }
                $admin = true;
                $this->editMember($admin);
                break;

            case 'edit_my_member':
                if (empty($this->rolodex->member)) {
                    $this->rolodex->loadMember($_REQUEST['user_id']); // I don't think the id in here matters but am unsure?
                }
                if (!isset($this->rolodex->member->user_id)) {
                    $this->rolodex->member->user_id = $_REQUEST['user_id'];
                }
                if ($this->rolodex->member->isMine()) {
                    $admin = false;
                    $this->editMember($admin);
                } else {
                    $this->rolodex->forwardMessage(dgettext('rolodex', 'You may only edit your own profile.'));
                    PHPWS_Core::reroute('index.php?module=rolodex&uop=list');
                }
                break;

            case 'message_member':
                if (empty($this->rolodex->member)) {
                    $this->rolodex->loadMember();
                }
                $this->contactMember();
                break;

            case 'edit_location':
                if (empty($this->rolodex->location)) {
                    $this->rolodex->loadLocation();
                }
                $this->rolodex->panel->setCurrentTab('locations');
                $this->editLocation();
                break;

            case 'edit_feature':
                if (empty($this->rolodex->feature)) {
                    $this->rolodex->loadFeature();
                }
                $this->rolodex->panel->setCurrentTab('features');
                $this->editFeature();
                break;

            case 'list':
                $this->rolodex->panel->setCurrentTab('list');
                $this->listMembers(1);
                break;

            case 'approvals':
                $this->rolodex->panel->setCurrentTab('approvals');
                $this->listMembers(0);
                break;

            case 'expired':
                $this->rolodex->panel->setCurrentTab('expired');
                $this->listMembers(null, true);
                break;

            case 'utilities':
                $this->rolodex->panel->setCurrentTab('utilities');
                $this->utilities();
                break;

            case 'settings':
                $this->rolodex->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'locations':
                $this->rolodex->panel->setCurrentTab('locations');
                $this->listLocations();
                break;

            case 'features':
                $this->rolodex->panel->setCurrentTab('features');
                $this->listFeatures();
                break;

            case 'info':
                $this->rolodex->panel->setCurrentTab('info');
                $this->showInfo();
                break;
        }
    }

    public function settingsPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=rolodex&aop=menu';

        if (Current_User::allow('rolodex', 'settings', null, null, true)) {
            $tags['settings'] = array('title' => dgettext('rolodex', 'Settings'),
                'link' => $link);
            if (PHPWS_Settings::get('rolodex', 'use_locations')) {
                $tags['locations'] = array('title' => dgettext('rolodex', 'Locations'),
                    'link' => $link);
            }
            if (PHPWS_Settings::get('rolodex', 'use_features')) {
                $tags['features'] = array('title' => dgettext('rolodex', 'Features'),
                    'link' => $link);
            }

            $tags['utilities'] = array('title' => dgettext('rolodex', 'Utilities'), 'link' => $link);
        }

        $panel = new PHPWS_Panel('rolodex-settings-panel');
        $panel->quickSetTabs($tags);
        $panel->setModule('rolodex');
        return $panel;
    }

    public function listMembers($approved=null, $expired=false, $location=null, $feature=null, $category=null)
    {
//print_r($_REQUEST); exit;
        /* init the classes */
        PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        /* add the alpha click list for approved lists */
        if (isset($approved) && $approved == 1) {
            $ptags['ALPHA_CLICK'] = $this->rolodex->alpha_click();
        }

        /* set the column headings */
        $ptags['TITLE_HEADER'] = dgettext('rolodex', 'Title');
        $ptags['DATE_UPDATED_HEADER'] = dgettext('rolodex', 'Updated');
        if (Rolodex_Member::isDataVisible('privacy_contact')) {
            $ptags['CONTACT_EMAIL_HEADER'] = dgettext('rolodex', 'Contact');
        }
        if (Rolodex_Member::isDataVisible('privacy_web')) {
            $ptags['WEBSITE_HEADER'] = dgettext('rolodex', 'Web');
        }

        /* check for custom fields to include */
        if (PHPWS_Settings::get('rolodex', 'custom1_name') && PHPWS_Settings::get('rolodex', 'custom1_list')) {
            $ptags['CUSTOM1_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom1_name'));
        } else {
            $ptags['CUSTOM1_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom2_name') && PHPWS_Settings::get('rolodex', 'custom2_list')) {
            $ptags['CUSTOM2_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom2_name'));
        } else {
            $ptags['CUSTOM2_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom3_name') && PHPWS_Settings::get('rolodex', 'custom3_list')) {
            $ptags['CUSTOM3_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom3_name'));
        } else {
            $ptags['CUSTOM3_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom4_name') && PHPWS_Settings::get('rolodex', 'custom4_list')) {
            $ptags['CUSTOM4_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom4_name'));
        } else {
            $ptags['CUSTOM4_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom5_name') && PHPWS_Settings::get('rolodex', 'custom5_list') && Current_User::allow('rolodex', 'view_privates')) {
            $ptags['CUSTOM5_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom5_name'));
        } else {
            $ptags['CUSTOM5_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom6_name') && PHPWS_Settings::get('rolodex', 'custom6_list') && Current_User::allow('rolodex', 'view_privates')) {
            $ptags['CUSTOM6_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom6_name'));
        } else {
            $ptags['CUSTOM6_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom7_name') && PHPWS_Settings::get('rolodex', 'custom7_list') && Current_User::allow('rolodex', 'view_privates')) {
            $ptags['CUSTOM7_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom7_name'));
        } else {
            $ptags['CUSTOM7_SORT'] = null;
        }
        if (PHPWS_Settings::get('rolodex', 'custom8_name') && PHPWS_Settings::get('rolodex', 'custom8_list') && Current_User::allow('rolodex', 'view_privates')) {
            $ptags['CUSTOM8_HEADER'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('rolodex', 'custom8_name'));
        } else {
            $ptags['CUSTOM8_SORT'] = null;
        }


        /* init the pager */
        $pager = new DBPager('rolodex_member', 'Rolodex_Member');
        $pager->setModule('rolodex');
        $pager->db->addColumn('rolodex_member.*');
        $pager->db->addColumn('demographics.*');


        /* approved yes/no */
        if (isset($approved)) {
            $pager->addWhere('active', $approved);
        }

        /* expired yes/no */
        if (PHPWS_Settings::get('rolodex', 'enable_expiry')) {
            if ($expired) {
                $pager->addWhere('date_expires', time(), '<=');
            } else {
                $pager->addWhere('date_expires', time(), '>=');
            }
        }

        /* make sure only unrestricted users see inactive members */
        if (!Current_User::isUnrestricted('rolodex')) {
            $pager->db->addWhere('active', 1);
        }

        /* set the default sort order and title column sort */
        if (PHPWS_Settings::get('rolodex', 'sortby')) {
            $sortby = 'demographics.last_name';
            $pager->joinResult('user_id', 'demographics', 'user_id', 'last_name', 'title');
        } else {
            $sortby = 'demographics.business_name';
            $pager->joinResult('user_id', 'demographics', 'user_id', 'business_name', 'title');
        }

        /* deal with privacy levels */
        if (!$_SESSION['User']->id) {
            $pager->db->addWhere('rolodex_member.privacy', 0);
        } elseif (!Current_User::allow('rolodex', 'view_privates')) {
            $pager->db->addWhere('rolodex_member.privacy', 1, '<=');
        }

        /* unset the filters if a search is being done */
//        if (isset($_REQUEST['search']) && $_REQUEST['clearFilters']) {
        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            unset($_REQUEST['browseLetter']);
            unset($_REQUEST['locations']);
            unset($_REQUEST['features']);
            unset($_REQUEST['categories']);
        }

        /* if the alpha click list is being used */
        if (isset($_REQUEST['browseLetter'])) {
            if ($_REQUEST['browseLetter'] == 'Other') {
                $pager->db->addWhere($sortby, '^[^a-z]', 'REGEXP');
            } else {
                $pager->db->addWhere($sortby, $_REQUEST['browseLetter'] . '%', 'LIKE');
            }
        }

        /* if it's a list by location */
        if ($location) {
            $_REQUEST['locations'] = $location;
            PHPWS_Core::initModClass('rolodex', 'RDX_Location.php');
            $item = new Rolodex_Location($location);
            Layout::addPageTitle($item->getTitle());
            $ptags['ITEM_TITLE'] = $item->getTitle(true);
            $ptags['ITEM_DESCRIPTION'] = PHPWS_Text::parseTag($item->getDescription(true));
            $ptags['ITEM_IMAGE'] = $item->getFile();
            if ($item->getFile()) {
                $ptags['ITEM_IMAGE'] = $item->getFile();
                $ptags['ITEM_CLEAR_FLOAT'] = '<br style="clear: right;" />';
            }
        }
        /* search by location */
        if (isset($_REQUEST['locations'])) {
            $pager->db->addColumn('rolodex_location_items.*');
            $pager->db->addWhere('rolodex_member.user_id', 'rolodex_location_items.member_id');
            $pager->db->addWhere('rolodex_location_items.location_id', $_REQUEST['locations']);
            $pager->db->addGroupBy('rolodex_location_items.member_id');
        }

        /* if it's a list by feature */
        if ($feature) {
            $_REQUEST['features'] = $feature;
            PHPWS_Core::initModClass('rolodex', 'RDX_Feature.php');
            $item = new Rolodex_Feature($feature);
            Layout::addPageTitle($item->getTitle());
            $ptags['ITEM_TITLE'] = $item->getTitle(true);
            $ptags['ITEM_DESCRIPTION'] = PHPWS_Text::parseTag($item->getDescription(true));
            $ptags['ITEM_IMAGE'] = $item->getFile();
            if ($item->getFile()) {
                $ptags['ITEM_IMAGE'] = $item->getFile();
                $ptags['ITEM_CLEAR_FLOAT'] = '<br style="clear: right;" />';
            }
        }
        /* search by feature */
        if (isset($_REQUEST['features'])) {
            $pager->db->addColumn('rolodex_feature_items.*');
            $pager->db->addWhere('rolodex_member.user_id', 'rolodex_feature_items.member_id');
            $pager->db->addWhere('rolodex_feature_items.feature_id', $_REQUEST['features']);
            $pager->db->addGroupBy('rolodex_feature_items.member_id');
        }

        /* if it's a list by category */
        if ($category) {
            $_REQUEST['categories'] = $category;
            PHPWS_Core::initModClass('categories', 'Category.php');
            $item = new Category($category);
            Layout::addPageTitle($item->getTitle());
            $ptags['ITEM_TITLE'] = $item->getTitle();
            $ptags['ITEM_DESCRIPTION'] = PHPWS_Text::parseTag($item->getDescription());
            $ptags['ITEM_IMAGE'] = $item->getIcon();
            if ($item->getIcon()) {
                $ptags['ITEM_IMAGE'] = $item->getIcon();
                $ptags['ITEM_CLEAR_FLOAT'] = '<br style="clear: right;" />';
            }
        }
        /* search by category */
        if (isset($_REQUEST['categories'])) {
            $pager->db->addColumn('category_items.*');
            $pager->db->addWhere('rolodex_member.key_id', 'category_items.key_id');
            $pager->db->addWhere('category_items.cat_id', $_REQUEST['categories']);
            $pager->db->addGroupBy('category_items.key_id');
        }

        /* the default sort order */
        $pager->setOrder($sortby, 'asc', true);


        /* setup the list page */
        $pager->setTemplate('list_member.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            if (Current_User::allow('rolodex', 'edit_member')) {
                $vars['aop'] = 'menu';
                $vars['tab'] = 'settings';
                $vars2['aop'] = 'menu';
                $vars2['tab'] = 'new';
                if (isset($approved) && $approved == 1) {
                    $pager->setEmptyMessage(sprintf(dgettext('rolodex', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('rolodex', 'Settings'), 'rolodex', $vars), PHPWS_Text::secureLink(dgettext('rolodex', 'New Member'), 'rolodex', $vars2)));
                }
            } else {
                $pager->setEmptyMessage(dgettext('rolodex', 'Sorry, no members are available at this time. Try broadening your search or returning later.'));
            }
        }

//        $ptags['CLEAR_FILTERS'] = '<input type="radio" name="clearFilters" value="1" checked="checked" />' . dgettext('rolodex', 'Clear filters or');
//        $ptags['CLEAR_FILTERS'] .= ' <input type="radio" name="clearFilters" value="0" />' . dgettext('rolodex', 'Search within results');
        if (isset($ptags['ITEM_TITLE'])) {
            $ptags['CLEAR_FILTERS'] = sprintf(dgettext('rolodex', 'Search within %s.'), $ptags['ITEM_TITLE']);
        } else {
            $ptags['CLEAR_FILTERS'] = dgettext('rolodex', 'Clears filters and searches all records.');
        }

        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
//        $pager->setSearch('last_name','title'); // the alias doesn't work here so we do the below
        $pager->setSearch('demographics.business_name', 'demographics.first_name', 'demographics.last_name', 'rolodex_member.description');

        /* debug stuff */
//        print_r($pager);
//        $pager->db->setTestMode();

        /* get the final content */
        $this->rolodex->content = $pager->get();

        /* don't remember what I was testing/doing with the next line doh! */
//        $this->rolodex->content .= Categories::getCategoryList('rolodex');

        /* set the list/page title */
        if (isset($approved) && $approved == 0) {
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Unapproved %s Members'), PHPWS_Settings::get('rolodex', 'module_title'));
        } elseif ($expired) {
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Expired %s Members'), PHPWS_Settings::get('rolodex', 'module_title'));
        } else {
            $this->rolodex->title = sprintf(dgettext('rolodex', '%s Members'), PHPWS_Settings::get('rolodex', 'module_title'));
            if (isset($ptags['ITEM_TITLE'])) {
                $this->rolodex->title .= sprintf(dgettext('rolodex', ' - %s'), $ptags['ITEM_TITLE']);
            }
        }
    }

    public function listLocations()
    {
        $ptags['TITLE_HEADER'] = dgettext('rolodex', 'Title');
        $ptags['DESCRIPTION_HEADER'] = dgettext('rolodex', 'Description');
        $ptags['ALPHA_CLICK'] = $this->rolodex->alpha_click();

        PHPWS_Core::initModClass('rolodex', 'RDX_Location.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('rolodex_location', 'Rolodex_Location');
        $pager->setModule('rolodex');
//        $pager->setOrder('title', 'asc', true);
        $pager->setDefaultOrder('title', 'asc', true);
        $pager->setTemplate('list_location.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            if (Current_User::allow('rolodex', 'settings', null, null, true)) {
                $vars['aop'] = 'menu';
                $vars['tab'] = 'settings';
                $vars2['aop'] = 'new_location';
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('rolodex', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('rolodex', 'Settings'), 'rolodex', $vars), PHPWS_Text::secureLink(dgettext('rolodex', 'New Location'), 'rolodex', $vars2));
            } else {
                $ptags['EMPTY_MESSAGE'] = dgettext('rolodex', 'Sorry, no locations are available at this time.');
            }
        }
        if (Current_User::allow('rolodex', 'settings', null, null, true)) {
            $vars3['aop'] = 'new_location';
            $label = Icon::show('add', dgettext('rolodex', 'Add Location'));
            $ptags['ADD_LINK'] = PHPWS_Text::secureLink($label . ' ' . dgettext('rolodex', 'Add Location'), 'rolodex', $vars3);
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');
//        $pager->addSortHeader('title', dgettext('rolodex', 'Title'));

        $this->rolodex->content = $pager->get();
        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Locations'), PHPWS_Settings::get('rolodex', 'module_title'));
    }

    public function listFeatures()
    {
        $ptags['TITLE_HEADER'] = dgettext('rolodex', 'Title');
        $ptags['DESCRIPTION_HEADER'] = dgettext('rolodex', 'Description');
        $ptags['ALPHA_CLICK'] = $this->rolodex->alpha_click();

        PHPWS_Core::initModClass('rolodex', 'RDX_Feature.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('rolodex_feature', 'Rolodex_feature');
        $pager->setModule('rolodex');
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_feature.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            if (Current_User::allow('rolodex', 'settings', null, null, true)) {
                $vars['aop'] = 'menu';
                $vars['tab'] = 'settings';
                $vars2['aop'] = 'new_feature';
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('rolodex', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('rolodex', 'Settings'), 'rolodex', $vars), PHPWS_Text::secureLink(dgettext('rolodex', 'New Feature'), 'rolodex', $vars2));
            } else {
                $ptags['EMPTY_MESSAGE'] = dgettext('rolodex', 'Sorry, no features are available at this time.');
            }
        }
        if (Current_User::allow('rolodex', 'settings', null, null, true)) {
            $vars3['aop'] = 'new_feature';
            $label = Icon::show('add', dgettext('rolodex', 'Add Feature'));
            $ptags['ADD_LINK'] = PHPWS_Text::secureLink($label . ' ' . dgettext('rolodex', 'Add Feature'), 'rolodex', $vars3);
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

        $this->rolodex->content = $pager->get();
        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Features'), PHPWS_Settings::get('rolodex', 'module_title'));
    }

    public function listCategories()
    {

        $ptags['TITLE_HEADER'] = dgettext('rolodex', 'Title');
        $ptags['DESCRIPTION_HEADER'] = dgettext('rolodex', 'Description');
        $ptags['ALPHA_CLICK'] = $this->rolodex->alpha_click();

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('categories');
        $pager->setModule('rolodex');
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_category.tpl');
        $num = $pager->getTotalRows();
        $pager->addRowFunction(array('Rolodex_Forms', 'listCatsRow'));
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

//        $pager->db->setTestMode();
        $this->rolodex->content = $pager->get();
        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Categories'), PHPWS_Settings::get('rolodex', 'module_title'));
    }

    public function listCatsRow($value)
    {
        $db = new PHPWS_DB('category_items');
        $db->addWhere('cat_id', $value['id']);
        $db->addWhere('module', 'rolodex');
        $num = $db->count();
        $tpl['TITLE'] = PHPWS_Text::moduleLink($value['title'], "rolodex", array('uop' => 'view_category', 'category' => $value['id'])) . ' (' . $num . ')';

        if (empty($value['description'])) {
            $tpl['DESCRIPTION'] = '';
        } else {
            $tpl['DESCRIPTION'] = substr(ltrim(strip_tags(str_replace('<br />', ' ', PHPWS_Text::parseOutput($value['description'])))), 0, 120) . ' ...';
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $tpl['ICON'] = Cabinet::getTag($value['icon']);

        return $tpl;
    }

    public function advSearchForm()
    {
        $form = new PHPWS_Form('rolodex_adv_search');

        $form->setMethod('get');
        $form->addHidden('module', 'rolodex');
        $form->addHidden('uop', 'adv_search');
//        $form->addHidden('uop', 'adv_search');
        $form->addText('pager_c_search');

        $form->addSubmit(dgettext('rolodex', 'Go'));
        $tpl = $form->getTemplate();

        $tpl['ALPHA_CLICK'] = $this->rolodex->alpha_click();
        if (PHPWS_Settings::get('rolodex', 'use_locations')) {
            $tpl['LOCATION_SELECT'] = $this->rolodex->getItemSelect('location', null, 'locations');
            $tpl['LOCATION_LABEL'] = dgettext('rolodex', 'Location(s)');
        }
        if (PHPWS_Settings::get('rolodex', 'use_features')) {
            $tpl['FEATURE_SELECT'] = $this->rolodex->getItemSelect('feature', null, 'features');
            $tpl['FEATURE_LABEL'] = dgettext('rolodex', 'Feature(s)');
        }
        if (PHPWS_Settings::get('rolodex', 'use_categories')) {
            $tpl['CATEGORY_SELECT'] = $this->rolodex->getCatSelect(null, 'categories');
            $tpl['CATEGORY_LABEL'] = dgettext('rolodex', 'Category(s)');
        }
        $tpl['CRITERIA_LABEL'] = dgettext('rolodex', 'Criteria');
        $tpl['SEARCH_LABEL'] = dgettext('rolodex', 'Search');
        $tpl['TIP_SELECT'] = dgettext('rolodex', 'Select one or more of the available options below to filter a list of members. It is possible to get too specific. If your search returns an empty list, try selecting fewer criteria.');
        $tpl['TIP_MULTI'] = dgettext('rolodex', 'To select more than one itme from any given list, click on the first item then hold your control key and click on the next. Use shift-click to select a range of items. Mac users, use command-click and shift-click.');

        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Advanced Search'), PHPWS_Settings::get('rolodex', 'module_title'));
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'adv_search_form.tpl');
    }

    public function editSettings()
    {

        $form = new PHPWS_Form('rolodex_settings');
        $form->addHidden('module', 'rolodex');
        $form->addHidden('aop', 'post_settings');

        $form->addText('module_title', PHPWS_Settings::get('rolodex', 'module_title'));
        $form->setSize('module_title', 30);
        $form->setLabel('module_title', dgettext('rolodex', 'The display title for this module, eg. Members, or Businesses, or Contacts'));

        $form->addCheckbox('allow_anon', 1);
        $form->setMatch('allow_anon', PHPWS_Settings::get('rolodex', 'allow_anon'));
        $form->setLabel('allow_anon', dgettext('rolodex', 'Allow anonymous viewing'));

        $form->addRadio('sortby', array(0, 1));
        $form->setLabel('sortby', array(dgettext('rolodex', 'Business name'), dgettext('rolodex', 'Last name')));
        $form->setMatch('sortby', PHPWS_Settings::get('rolodex', 'sortby'));

        $form->addCheckbox('req_approval', 1);
        $form->setMatch('req_approval', PHPWS_Settings::get('rolodex', 'req_approval'));
        $form->setLabel('req_approval', dgettext('rolodex', 'Require approval for new profiles'));

        $form->addCheckbox('send_notification', 1);
        $form->setMatch('send_notification', PHPWS_Settings::get('rolodex', 'send_notification'));
        $form->setLabel('send_notification', dgettext('rolodex', 'Send notification of pending applications'));

        $form->addCheckbox('notify_all_saves', 1);
        $form->setMatch('notify_all_saves', PHPWS_Settings::get('rolodex', 'notify_all_saves'));
        $form->setLabel('notify_all_saves', dgettext('rolodex', 'Send notification upon all member edits (not those done by an admin)'));

        $form->addText('admin_contact', PHPWS_Settings::get('rolodex', 'admin_contact'));
        $form->setSize('admin_contact', 30);
        $form->setLabel('admin_contact', dgettext('rolodex', 'The admin contact email address for this module'));

        $form->addCheckbox('use_categories', 1);
        $form->setMatch('use_categories', PHPWS_Settings::get('rolodex', 'use_categories'));
        $form->setLabel('use_categories', dgettext('rolodex', 'Enable Categories (uses core Categories)'));

        $form->addCheckbox('use_locations', 1);
        $form->setMatch('use_locations', PHPWS_Settings::get('rolodex', 'use_locations'));
        $form->setLabel('use_locations', dgettext('rolodex', 'Enable Locations'));

        $form->addCheckbox('use_features', 1);
        $form->setMatch('use_features', PHPWS_Settings::get('rolodex', 'use_features'));
        $form->setLabel('use_features', dgettext('rolodex', 'Enable Features'));

        $form->addCheckbox('comments_enable', 1);
        $form->setMatch('comments_enable', PHPWS_Settings::get('rolodex', 'comments_enable'));
        $form->setLabel('comments_enable', dgettext('rolodex', 'Enable comment settings on profiles (required for the rest of the Comments Settings)'));

        $form->addCheckbox('comments_enforce', 1);
        $form->setMatch('comments_enforce', PHPWS_Settings::get('rolodex', 'comments_enforce'));
        $form->setLabel('comments_enforce', dgettext('rolodex', 'Force comments enabled, on all profiles'));

        $form->addCheckbox('comments_anon_enable', 1);
        $form->setMatch('comments_anon_enable', PHPWS_Settings::get('rolodex', 'comments_anon_enable'));
        $form->setLabel('comments_anon_enable', dgettext('rolodex', 'Enable anonymous comment settings on profiles (required for the next Force anonymous setting)'));

        $form->addCheckbox('comments_anon_enforce', 1);
        $form->setMatch('comments_anon_enforce', PHPWS_Settings::get('rolodex', 'comments_anon_enforce'));
        $form->setLabel('comments_anon_enforce', dgettext('rolodex', 'Force anonymous comments enabled, on all profiles'));

        $form->addRadio('contact_type', array(0, 1));
        $form->setLabel('contact_type', array(dgettext('rolodex', 'Email link'), dgettext('rolodex', 'Web form')));
        $form->setMatch('contact_type', PHPWS_Settings::get('rolodex', 'contact_type'));

        $form->addRadio('privacy_contact', array(0, 1, 2));
        $form->setLabel('privacy_contact', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_contact', PHPWS_Settings::get('rolodex', 'privacy_contact'));

        $form->addRadio('privacy_web', array(0, 1, 2));
        $form->setLabel('privacy_web', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_web', PHPWS_Settings::get('rolodex', 'privacy_web'));

        $form->addRadio('privacy_home_phone', array(0, 1, 2));
        $form->setLabel('privacy_home_phone', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_home_phone', PHPWS_Settings::get('rolodex', 'privacy_home_phone'));

        $form->addRadio('privacy_bus_phone', array(0, 1, 2));
        $form->setLabel('privacy_bus_phone', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_bus_phone', PHPWS_Settings::get('rolodex', 'privacy_bus_phone'));

        $form->addRadio('privacy_home', array(0, 1, 2));
        $form->setLabel('privacy_home', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_home', PHPWS_Settings::get('rolodex', 'privacy_home'));

        $form->addRadio('privacy_business', array(0, 1, 2));
        $form->setLabel('privacy_business', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_business', PHPWS_Settings::get('rolodex', 'privacy_business'));

        $form->addRadio('privacy_export', array(0, 1, 2));
        $form->setLabel('privacy_export', array(dgettext('rolodex', 'Public'), dgettext('rolodex', 'Members only'), dgettext('rolodex', 'Restricted users')));
        $form->setMatch('privacy_export', PHPWS_Settings::get('rolodex', 'privacy_export'));

        $form->addRadio('privacy_use_search', array(0, 1));
        $form->setLabel('privacy_use_search', array(dgettext('rolodex', 'No'), dgettext('rolodex', 'Yes')));
        $form->setMatch('privacy_use_search', PHPWS_Settings::get('rolodex', 'privacy_use_search'));

        $form->addCheckbox('enable_expiry', 1);
        $form->setMatch('enable_expiry', PHPWS_Settings::get('rolodex', 'enable_expiry'));
        $form->setLabel('enable_expiry', dgettext('rolodex', 'Enable expiry on member profiles'));

        $form->addText('expiry_interval', PHPWS_Settings::get('rolodex', 'expiry_interval'));
        $form->setSize('expiry_interval', 4, 4);
        $form->setLabel('expiry_interval', dgettext('rolodex', 'Default expiry interval (in days), if expiry is enabled'));

        $form->addCheckbox('use_captcha', 1);
        $form->setMatch('use_captcha', PHPWS_Settings::get('rolodex', 'use_captcha'));
        $form->setLabel('use_captcha', dgettext('rolodex', 'Use graphical confirmation on contact form (CAPTCHA)'));

        $form->addRadio('list_address', array(0, 1, 2, 3));
        $form->setLabel('list_address', array(dgettext('rolodex', 'None'), dgettext('rolodex', 'Business'), dgettext('rolodex', 'Home'), dgettext('rolodex', 'Both')));
        $form->setMatch('list_address', PHPWS_Settings::get('rolodex', 'list_address'));

        $form->addCheckbox('list_phone', 1);
        $form->setMatch('list_phone', PHPWS_Settings::get('rolodex', 'list_phone'));
        $form->setLabel('list_phone', dgettext('rolodex', 'Display phone numbers'));

        $form->addCheckbox('list_categories', 1);
        $form->setMatch('list_categories', PHPWS_Settings::get('rolodex', 'list_categories'));
        $form->setLabel('list_categories', dgettext('rolodex', 'Display categories'));

        $form->addCheckbox('list_locations', 1);
        $form->setMatch('list_locations', PHPWS_Settings::get('rolodex', 'list_locations'));
        $form->setLabel('list_locations', dgettext('rolodex', 'Display locations'));

        $form->addCheckbox('list_features', 1);
        $form->setMatch('list_features', PHPWS_Settings::get('rolodex', 'list_features'));
        $form->setLabel('list_features', dgettext('rolodex', 'Display features'));

        $form->addTextField('max_img_width', PHPWS_Settings::get('rolodex', 'max_img_width'));
        $form->setLabel('max_img_width', dgettext('rolodex', 'Maximum image width (50-600)'));
        $form->setSize('max_img_width', 4, 4);

        $form->addTextField('max_img_height', PHPWS_Settings::get('rolodex', 'max_img_height'));
        $form->setLabel('max_img_height', dgettext('rolodex', 'Maximum image height (50-600)'));
        $form->setSize('max_img_height', 4, 4);

        $form->addTextField('max_thumb_width', PHPWS_Settings::get('rolodex', 'max_thumb_width'));
        $form->setLabel('max_thumb_width', dgettext('rolodex', 'Maximum thumbnail width (40-200)'));
        $form->setSize('max_thumb_width', 4, 4);

        $form->addTextField('max_thumb_height', PHPWS_Settings::get('rolodex', 'max_thumb_height'));
        $form->setLabel('max_thumb_height', dgettext('rolodex', 'Maximum thumbnail height (40-200)'));
        $form->setSize('max_thumb_height', 4, 4);

        $form->addTextField('other_img_width', PHPWS_Settings::get('rolodex', 'other_img_width'));
        $form->setLabel('other_img_width', dgettext('rolodex', 'Maximum image width for locations/features (20-400)'));
        $form->setSize('other_img_width', 4, 4);

        $form->addTextField('other_img_height', PHPWS_Settings::get('rolodex', 'other_img_height'));
        $form->setLabel('other_img_height', dgettext('rolodex', 'Maximum image height for locations/features (20-400)'));
        $form->setSize('other_img_height', 4, 4);

        $form->addCheckbox('show_block', 1);
        $form->setMatch('show_block', PHPWS_Settings::get('rolodex', 'show_block'));
        $form->setLabel('show_block', dgettext('rolodex', 'Show rolodex block'));

        $form->addRadio('block_order_by_rand', array(0, 1));
        $form->setLabel('block_order_by_rand', array(dgettext('rolodex', 'Most recent'), dgettext('rolodex', 'Random')));
        $form->setMatch('block_order_by_rand', PHPWS_Settings::get('rolodex', 'block_order_by_rand'));

        $form->addCheckbox('block_on_home_only', 1);
        $form->setMatch('block_on_home_only', PHPWS_Settings::get('rolodex', 'block_on_home_only'));
        $form->setLabel('block_on_home_only', dgettext('rolodex', 'Show on home only'));

        $form->addText('custom1_name', PHPWS_Settings::get('rolodex', 'custom1_name'));
        $form->setLabel('custom1_name', dgettext('rolodex', 'Custom field one name'));
        $form->setSize('custom1_name', 30);

        $form->addText('custom2_name', PHPWS_Settings::get('rolodex', 'custom2_name'));
        $form->setLabel('custom2_name', dgettext('rolodex', 'Custom field two name'));
        $form->setSize('custom2_name', 30);

        $form->addText('custom3_name', PHPWS_Settings::get('rolodex', 'custom3_name'));
        $form->setLabel('custom3_name', dgettext('rolodex', 'Custom field three name'));
        $form->setSize('custom3_name', 30);

        $form->addText('custom4_name', PHPWS_Settings::get('rolodex', 'custom4_name'));
        $form->setLabel('custom4_name', dgettext('rolodex', 'Custom field four name'));
        $form->setSize('custom4_name', 30);

        $form->addText('custom5_name', PHPWS_Settings::get('rolodex', 'custom5_name'));
        $form->setLabel('custom5_name', dgettext('rolodex', 'Custom field five name (internal use only)'));
        $form->setSize('custom5_name', 30);

        $form->addText('custom6_name', PHPWS_Settings::get('rolodex', 'custom6_name'));
        $form->setLabel('custom6_name', dgettext('rolodex', 'Custom field six name (internal use only)'));
        $form->setSize('custom6_name', 30);

        $form->addText('custom7_name', PHPWS_Settings::get('rolodex', 'custom7_name'));
        $form->setLabel('custom7_name', dgettext('rolodex', 'Custom field seven name (internal use only)'));
        $form->setSize('custom7_name', 30);

        $form->addText('custom8_name', PHPWS_Settings::get('rolodex', 'custom8_name'));
        $form->setLabel('custom8_name', dgettext('rolodex', 'Custom field eight name (internal use only)'));
        $form->setSize('custom8_name', 30);

        $form->addCheckbox('custom1_list', 1);
        $form->setMatch('custom1_list', PHPWS_Settings::get('rolodex', 'custom1_list'));

        $form->addCheckbox('custom2_list', 1);
        $form->setMatch('custom2_list', PHPWS_Settings::get('rolodex', 'custom2_list'));

        $form->addCheckbox('custom3_list', 1);
        $form->setMatch('custom3_list', PHPWS_Settings::get('rolodex', 'custom3_list'));

        $form->addCheckbox('custom4_list', 1);
        $form->setMatch('custom4_list', PHPWS_Settings::get('rolodex', 'custom4_list'));

        $form->addCheckbox('custom5_list', 1);
        $form->setMatch('custom5_list', PHPWS_Settings::get('rolodex', 'custom5_list'));

        $form->addCheckbox('custom6_list', 1);
        $form->setMatch('custom6_list', PHPWS_Settings::get('rolodex', 'custom6_list'));

        $form->addCheckbox('custom7_list', 1);
        $form->setMatch('custom7_list', PHPWS_Settings::get('rolodex', 'custom7_list'));

        $form->addCheckbox('custom8_list', 1);
        $form->setMatch('custom8_list', PHPWS_Settings::get('rolodex', 'custom8_list'));



        $form->addSubmit('save', dgettext('rolodex', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_GROUP_LABEL'] = dgettext('rolodex', 'General Settings');
        $tpl['SORTBY_TITLE'] = dgettext('rolodex', 'Default sort lists by');
        $tpl['CONTACT_TYPE_TITLE'] = dgettext('rolodex', 'Contact link type');
        $tpl['PRIVACY_CONTACT_TITLE'] = dgettext('rolodex', 'Contact link');
        $tpl['PRIVACY_WEB_TITLE'] = dgettext('rolodex', 'Website link');
        $tpl['PRIVACY_HOME_PHONE_TITLE'] = dgettext('rolodex', 'Home phone numbers');
        $tpl['PRIVACY_BUS_PHONE_TITLE'] = dgettext('rolodex', 'Business phone numbers');
        $tpl['PRIVACY_HOME_TITLE'] = dgettext('rolodex', 'Home address');
        $tpl['PRIVACY_BUSINESS_TITLE'] = dgettext('rolodex', 'Business address');
        $tpl['PRIVACY_EXPORT_TITLE'] = dgettext('rolodex', 'CSV Export');
        $tpl['PRIVACY_USE_SEARCH_TITLE'] = dgettext('rolodex', 'Register records in search module');
        $tpl['LIST_SETTINGS_TIP'] = dgettext('rolodex', 'The following settings respect the privacy settings above and do not over-ride them.');
        $tpl['LIST_ADDRESS_TITLE'] = dgettext('rolodex', 'Address to display on browse list views');
        $tpl['OPTIONS_GROUP_LABEL'] = dgettext('rolodex', 'Organizing/Classifying Options');
        $tpl['COMMENTS_GROUP_LABEL'] = dgettext('rolodex', 'Comments Settings');
        $tpl['PRIVACY_GROUP_LABEL'] = dgettext('rolodex', 'Privacy Settings');
        $tpl['LIST_GROUP_LABEL'] = dgettext('rolodex', 'List Options');
        $tpl['IMAGE_GROUP_LABEL'] = dgettext('rolodex', 'Image Settings');
        $tpl['FIELDS_GROUP_LABEL'] = dgettext('rolodex', 'Custom Fields');
        $tpl['FIELDS_NOTE'] = dgettext('rolodex', 'You may use up to 8 custom fields. To add a field to your member profile template, enter a title for it here. Fields 1-4 follow whatever other security/privacy settings are in place. Fields 5-8 are only visible to Rolodex admins. Leave these fields empty if you do not wish to add custom fields to your profiles.');
        $tpl['FIELDS_LIST_LABEL'] = dgettext('rolodex', 'Select checkbox to also include field in list view');

        $this->rolodex->title = dgettext('rolodex', 'Settings');
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'edit_settings.tpl');
    }

    public function selectUser()
    {

        $form = new PHPWS_Form('rolodex_user');
        $form->addHidden('module', 'rolodex');
        $form->addHidden('aop', 'edit_member');

// I don't seem to need the class
//        PHPWS_Core::initModClass('users', 'Users.php');
        $db = new PHPWS_DB('users');
        $db->addColumn('id');
        $db->addColumn('username');
        $db->addColumn('display_name');
        $db->addJoin('left', 'users', 'rolodex_member', 'id', 'user_id');
        $db->addWhere('rolodex_member.user_id', null, 'is');
        $result = $db->getObjects('PHPWS_User');

        if ($result) {
            foreach ($result as $user) {
                $choices[$user->id] = $user->display_name;
            }
            $form->addSelect('user_id', $choices);
            $form->setLabel('user_id', dgettext('rolodex', 'Available users'));
            $form->addSubmit('save', dgettext('rolodex', 'Continue'));
        } else {
            $form->addTplTag('NO_USERS_NOTE', dgettext('rolodex', 'Sorry, there are no users available. You will have to create a user account first.'));
        }

        $tpl = $form->getTemplate();
        $tpl['USER_ID_GROUP_LABEL'] = dgettext('rolodex', 'User options');

        $this->rolodex->title = dgettext('rolodex', 'New member step one');
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'select_user.tpl');
    }

    public function editMember($admin=true)
    {
//print_r($this->rolodex->member);
        $form = new PHPWS_Form('rolodex_member');
        $member = & $this->rolodex->member;

        $form->addHidden('module', 'rolodex');

        if ($admin) {
            $form->addHidden('aop', 'post_member');
        } else {
            $form->addHidden('uop', 'post_member');
        }

        if ($member->user_id) {
            $form->addHidden('user_id', $member->user_id);
        }

        if ($member->isNew()) {
            if (PHPWS_Settings::get('rolodex', 'req_approval')) {
                $form->addSubmit('save', dgettext('rolodex', 'Submit'));
                $this->rolodex->title = dgettext('rolodex', 'Submit member profile');
            } else {
                $form->addSubmit('save', dgettext('rolodex', 'Create'));
                $this->rolodex->title = dgettext('rolodex', 'Create member profile');
            }
        } else {
            $form->addSubmit('save', dgettext('rolodex', 'Update'));
            $this->rolodex->title = dgettext('rolodex', 'Update member profile');
        }


        $form->addText('courtesy_title', $member->courtesy_title);
        $form->setSize('courtesy_title', 4);
        $form->setLabel('courtesy_title', dgettext('rolodex', 'Title'));

        $form->addText('first_name', $member->first_name);
        $form->setSize('first_name', 20);
        $form->setLabel('first_name', dgettext('rolodex', 'First name'));

        $form->addText('middle_initial', $member->middle_initial);
        $form->setSize('middle_initial', 1);
        $form->setLabel('middle_initial', dgettext('rolodex', 'I'));

        $form->addText('last_name', $member->last_name);
        $form->setSize('last_name', 30);
        $form->setLabel('last_name', dgettext('rolodex', 'Last name'));

        $form->addText('honorific', $member->honorific);
        $form->setSize('honorific', 4);
        $form->setLabel('honorific', dgettext('rolodex', 'Honorific'));

        $form->addText('business_name', $member->business_name);
        $form->setSize('business_name', 40);
        $form->setLabel('business_name', dgettext('rolodex', 'Business/Organization'));

        $form->addText('department', $member->department);
        $form->setSize('department', 30);
        $form->setLabel('department', dgettext('rolodex', 'Department'));

        $form->addText('position_title', $member->position_title);
        $form->setSize('position_title', 30);
        $form->setLabel('position_title', dgettext('rolodex', 'Position title'));

        $form->addTextArea('description', $member->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('rolodex', 'Description'));

        $form->addFile('image');
        $form->addTplTag('CURRENT_IMAGE', $member->getImage(true));
        $form->addTplTag('CURRENT_THUMB', $member->getThumbnail(true));
        $form->setLabel('image', dgettext('rolodex', 'New Image (Clears current image)'));

        $form->addCheckbox('clear_image', 1);
        $form->setLabel('clear_image', dgettext('rolodex', 'Check to clear image'));

        $form->addText('contact_email', $member->contact_email);
        $form->setSize('contact_email', 40);
        $form->setLabel('contact_email', sprintf(dgettext('rolodex', 'Contact e-mail (or leave empty to use %s)'), $member->getDisplay_email()));

        $form->addText('website', $member->website);
        $form->setSize('website', 40);
        $form->setLabel('website', dgettext('rolodex', 'Web site'));

        $form->addText('day_phone', $member->day_phone);
        $form->setSize('day_phone', 15);
        $form->setLabel('day_phone', dgettext('rolodex', 'Business phone'));

        $form->addText('day_phone_ext', $member->day_phone_ext);
        $form->setSize('day_phone_ext', 4);
        $form->setLabel('day_phone_ext', dgettext('rolodex', 'Extension'));

        $form->addText('evening_phone', $member->evening_phone);
        $form->setSize('evening_phone', 15);
        $form->setLabel('evening_phone', dgettext('rolodex', 'Home phone'));

        $form->addText('fax_number', $member->fax_number);
        $form->setSize('fax_number', 15);
        $form->setLabel('fax_number', dgettext('rolodex', 'Fax'));

        $form->addText('tollfree_phone', $member->tollfree_phone);
        $form->setSize('tollfree_phone', 15);
        $form->setLabel('tollfree_phone', dgettext('rolodex', 'Toll-free phone'));

        $form->addText('mailing_address_1', $member->mailing_address_1);
        $form->setSize('mailing_address_1', 60);
        $form->setLabel('mailing_address_1', dgettext('rolodex', 'Mailing address 1'));

        $form->addText('mailing_address_2', $member->mailing_address_2);
        $form->setSize('mailing_address_2', 60);
        $form->setLabel('mailing_address_2', dgettext('rolodex', 'Mailing address 2'));

        $form->addText('mailing_city', $member->mailing_city);
        $form->setSize('mailing_city', 40);
        $form->setLabel('mailing_city', dgettext('rolodex', 'City'));

        $form->addText('mailing_state', $member->mailing_state);
        $form->setSize('mailing_state', 3);
        $form->setLabel('mailing_state', dgettext('rolodex', 'Province/State'));

        $form->addText('mailing_country', $member->mailing_country);
        $form->setSize('mailing_country', 30);
        $form->setLabel('mailing_country', dgettext('rolodex', 'Country'));

        $form->addText('mailing_zip_code', $member->mailing_zip_code);
        $form->setSize('mailing_zip_code', 10);
        $form->setLabel('mailing_zip_code', dgettext('rolodex', 'Postal/Zip code'));

        $form->addText('business_address_1', $member->business_address_1);
        $form->setSize('business_address_1', 60);
        $form->setLabel('business_address_1', dgettext('rolodex', 'Business address 1'));

        $form->addText('business_address_2', $member->business_address_2);
        $form->setSize('business_address_2', 60);
        $form->setLabel('business_address_2', dgettext('rolodex', 'Business address 2'));

        $form->addText('business_city', $member->business_city);
        $form->setSize('business_city', 40);
        $form->setLabel('business_city', dgettext('rolodex', 'City'));

        $form->addText('business_state', $member->business_state);
        $form->setSize('business_state', 3);
        $form->setLabel('business_state', dgettext('rolodex', 'Province/State'));

        $form->addText('business_country', $member->business_country);
        $form->setSize('business_country', 30);
        $form->setLabel('business_country', dgettext('rolodex', 'Country'));

        $form->addText('business_zip_code', $member->business_zip_code);
        $form->setSize('business_zip_code', 10);
        $form->setLabel('business_zip_code', dgettext('rolodex', 'Postal/Zip code'));

        if (PHPWS_Settings::get('rolodex', 'comments_enable')) {
            if (PHPWS_Settings::get('rolodex', 'comments_enforce')) {
                $form->addHidden('allow_comments', 1);
            } else {
                $form->addCheckbox('allow_comments', 1);
                $form->setMatch('allow_comments', $member->allow_comments);
                $form->setLabel('allow_comments', dgettext('rolodex', 'Enable comments'));
            }
            if (PHPWS_Settings::get('rolodex', 'comments_anon_enable')) {
                if (PHPWS_Settings::get('rolodex', 'comments_anon_enforce')) {
                    $form->addHidden('allow_anon', 1);
                } else {
                    $form->addCheckbox('allow_anon', 1);
                    $form->setMatch('allow_anon', $member->allow_anon);
                    $form->setLabel('allow_anon', dgettext('rolodex', 'Allow anonymous comments'));
                }
            }
        }

        if (Current_User::allow('rolodex', 'edit_member')) {
            $form->addText('date_expires', $member->getDate_expires(true, '%Y/%m/%d'));
            $form->setSize('date_expires', 10);
            $form->setLabel('date_expires', dgettext('rolodex', 'Expiry date'));
        }

        if (Current_User::allow('rolodex', 'edit_member')) {
            $form->addCheckbox('active', 1);
            $form->setMatch('active', $member->active);
            $form->setLabel('active', dgettext('rolodex', 'Active'));
        }

        $choices = array('0' => dgettext('rolodex', 'Public'), '1' => dgettext('rolodex', 'Semi-private'), '2' => dgettext('rolodex', 'Private'));
        $form->addSelect('privacy', $choices);
        $form->setLabel('privacy', dgettext('rolodex', 'Privacy level'));
        $form->setMatch('privacy', $member->privacy);

        $form->addCheckbox('email_privacy', 1);
        $form->setMatch('email_privacy', $member->email_privacy);
        $form->setLabel('email_privacy', dgettext('rolodex', 'Hide Email/Contact link from everyone, except admins'));

        if (PHPWS_Settings::get('rolodex', 'custom1_name')) {
            $form->addText('custom1', $member->custom1);
            $form->setSize('custom1', 40);
            $form->setLabel('custom1', PHPWS_Settings::get('rolodex', 'custom1_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom2_name')) {
            $form->addText('custom2', $member->custom2);
            $form->setSize('custom2', 40);
            $form->setLabel('custom2', PHPWS_Settings::get('rolodex', 'custom2_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom3_name')) {
            $form->addText('custom3', $member->custom3);
            $form->setSize('custom3', 40);
            $form->setLabel('custom3', PHPWS_Settings::get('rolodex', 'custom3_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom4_name')) {
            $form->addText('custom4', $member->custom4);
            $form->setSize('custom4', 40);
            $form->setLabel('custom4', PHPWS_Settings::get('rolodex', 'custom4_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom5_name') && Current_User::allow('rolodex', 'edit_member')) {
            $form->addText('custom5', $member->custom5);
            $form->setSize('custom5', 40);
            $form->setLabel('custom5', PHPWS_Settings::get('rolodex', 'custom5_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom6_name') && Current_User::allow('rolodex', 'edit_member')) {
            $form->addText('custom6', $member->custom6);
            $form->setSize('custom6', 40);
            $form->setLabel('custom6', PHPWS_Settings::get('rolodex', 'custom6_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom7_name') && Current_User::allow('rolodex', 'edit_member')) {
            $form->addText('custom7', $member->custom7);
            $form->setSize('custom7', 40);
            $form->setLabel('custom7', PHPWS_Settings::get('rolodex', 'custom7_name'));
        }

        if (PHPWS_Settings::get('rolodex', 'custom8_name') && Current_User::allow('rolodex', 'edit_member')) {
            $form->addText('custom8', $member->custom8);
            $form->setSize('custom8', 40);
            $form->setLabel('custom8', PHPWS_Settings::get('rolodex', 'custom8_name'));
        }


        $tpl = $form->getTemplate();

        if (PHPWS_Settings::get('rolodex', 'use_categories')) {
            $tpl['CATEGORIES'] = Categories::getForm($match = $member->get_categories(), $select_name = 'categories', $multiple = true);
            $tpl['CATEGORIES_LABEL'] = dgettext('rolodex', 'Category(s)');
        }
        if (PHPWS_Settings::get('rolodex', 'use_locations')) {
            $tpl['LOCATIONS'] = $this->rolodex->getItemForm('location', $match = $member->get_locations(), $select_name = 'locations', $multiple = true);
            $tpl['LOCATIONS_LABEL'] = dgettext('rolodex', 'Location(s)');
        }
        if (PHPWS_Settings::get('rolodex', 'use_features')) {
            $tpl['FEATURES'] = $this->rolodex->getItemForm('feature', $match = $member->get_features(), $select_name = 'features', $multiple = true);
            $tpl['FEATURES_LABEL'] = dgettext('rolodex', 'Feature(s)');
        }

        if (Current_User::allow('users', 'edit_users')) {
            $tpl['EDIT_USER'] = $member->editUserLink();
            $tpl['ACTIVE_LINK'] = $member->activeLink();
        }

        if (Current_User::allow('rolodex', 'edit_member')) {
            $js_vars['form_name'] = 'rolodex_member';
            if (javascriptEnabled()) {
                $js_vars['date_name'] = 'date_expires';
                $tpl['EXPIRES_CAL'] = javascript('js_calendar', $js_vars);
            }
        }

        $tpl['PROFILE_GROUP_LABEL'] = dgettext('rolodex', 'Profile');
        $tpl['CONTACT_GROUP_LABEL'] = dgettext('rolodex', 'Contact information');
        $tpl['HOME_LABEL'] = dgettext('rolodex', 'Home address');
        $tpl['BUSINESS_LABEL'] = dgettext('rolodex', 'Business address');
        $tpl['SETTINGS_GROUP_LABEL'] = dgettext('rolodex', 'Settings');
        if (PHPWS_Settings::get('rolodex', 'use_categories') || PHPWS_Settings::get('rolodex', 'use_locations') || PHPWS_Settings::get('rolodex', 'use_features')) {
            $tpl['SELECT_LIST_TIP'] = dgettext('rolodex', 'To use the category, location, or feature lists, select an item from the list and click on the "+" symbol to add the selection to this profile. To remove a selection, click on the name of the item in the box below the select menu.');
        }
        if (PHPWS_Settings::get('rolodex', 'custom1_name') ||
                PHPWS_Settings::get('rolodex', 'custom2_name') ||
                PHPWS_Settings::get('rolodex', 'custom3_name') ||
                PHPWS_Settings::get('rolodex', 'custom4_name') ||
                (PHPWS_Settings::get('rolodex', 'custom5_name') && Current_User::allow('rolodex', 'edit_member')) ||
                (PHPWS_Settings::get('rolodex', 'custom6_name') && Current_User::allow('rolodex', 'edit_member')) ||
                (PHPWS_Settings::get('rolodex', 'custom7_name') && Current_User::allow('rolodex', 'edit_member')) ||
                (PHPWS_Settings::get('rolodex', 'custom8_name') && Current_User::allow('rolodex', 'edit_member'))
        ) {
            $tpl['META_GROUP_LABEL'] = dgettext('rolodex', 'Extra');
        }

        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'edit_member.tpl');
    }

    public function editLocation()
    {
        $form = new PHPWS_Form('rolodex_location');
        $location = & $this->rolodex->location;

        $form->addHidden('module', 'rolodex');
        $form->addHidden('aop', 'post_location');

        if ($location->id) {
            $form->addHidden('id', $location->id);
            $form->addSubmit(dgettext('rolodex', 'Update'));
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Update %s location'), PHPWS_Settings::get('rolodex', 'module_title'));
        } else {
            $form->addSubmit(dgettext('rolodex', 'Create'));
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Create %s location'), PHPWS_Settings::get('rolodex', 'module_title'));
        }

        $form->addText('title', $location->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('rolodex', 'Title'));

        $form->addTextArea('description', $location->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('rolodex', 'Description'));

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $location->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(PHPWS_Settings::get('rolodex', 'other_img_width'));
        $manager->maxImageHeight(PHPWS_Settings::get('rolodex', 'other_img_height'));

        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('rolodex', 'Details');

        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'edit_location.tpl');
    }

    public function editFeature()
    {
        $form = new PHPWS_Form('rolodex_feature');
        $feature = & $this->rolodex->feature;

        $form->addHidden('module', 'rolodex');
        $form->addHidden('aop', 'post_feature');

        if ($feature->id) {
            $form->addHidden('id', $feature->id);
            $form->addSubmit(dgettext('rolodex', 'Update'));
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Update %s feature'), PHPWS_Settings::get('rolodex', 'module_title'));
        } else {
            $form->addSubmit(dgettext('rolodex', 'Create'));
            $this->rolodex->title = sprintf(dgettext('rolodex', 'Create %s feature'), PHPWS_Settings::get('rolodex', 'module_title'));
        }

        $form->addText('title', $feature->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('rolodex', 'Title'));

        $form->addTextArea('description', $feature->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('rolodex', 'Description'));

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $feature->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(PHPWS_Settings::get('rolodex', 'other_img_width'));
        $manager->maxImageHeight(PHPWS_Settings::get('rolodex', 'other_img_height'));

        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('rolodex', 'Details');

        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'edit_feature.tpl');
    }

    public function contactMember()
    {
        if (isset($_POST['name'])) {
            $_POST['name'] = $_POST['name'];
        } else {
            $_POST['name'] = null;
        }
        if (isset($_POST['email'])) {
            $_POST['email'] = $_POST['email'];
        } else {
            $_POST['email'] = null;
        }
        if (isset($_POST['subject'])) {
            $_POST['subject'] = $_POST['subject'];
        } else {
            $_POST['subject'] = null;
        }
        if (isset($_POST['message'])) {
            $_POST['message'] = $_POST['message'];
        } else {
            $_POST['message'] = null;
        }
        $form = new PHPWS_Form;
        $form->addHidden('module', 'rolodex');
        $form->addHidden('uop', 'send_message');
        $form->addHidden('user_id', $this->rolodex->member->user_id);

        $form->addText('name', $_POST['name']);
        $form->setLabel('name', dgettext('rolodex', 'Your name'));
        $form->setSize('name', 40);

        $form->addText('email', $_POST['email']);
        $form->setLabel('email', dgettext('rolodex', 'Your email'));
        $form->setSize('email', 40);

        $form->addText('subject', $_POST['subject']);
        $form->setLabel('subject', dgettext('rolodex', 'Subject'));
        $form->setSize('subject', 40);

        $form->addTextArea('message', $_POST['message']);
        $form->setRows('message', '15');
        $form->setCols('message', '50');
        $form->setLabel('message', dgettext('rolodex', 'Message'));

        $form->addText('confirm_phrase');
        $form->setLabel('confirm_phrase', dgettext('rolodex', 'Confirm text'));

        if (PHPWS_Settings::get('rolodex', 'use_captcha') && extension_loaded('gd')) {
            $result = $this->confirmGraphic();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
            } else {
                $form->addTplTag('GRAPHIC', $result);
            }
        }

        $form->addSubmit('submit', dgettext('rolodex', 'Send Message'));

        $tpl = $form->getTemplate();
        $tpl['FORM_LABEL'] = dgettext('rolodex', 'Compose message');
        $tpl['FORM_INSTRUCTION'] = dgettext('rolodex', 'All fields are required.');
        $tpl['LINKS'] = implode(' | ', Rolodex::navLinks());

        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'message_member.tpl');
    }

    public function confirmGraphic()
    {
        PHPWS_Core::initCoreClass('Captcha.php');
        return Captcha::get();
    }

    public function utilities()
    {

        $tpl['MISC_GROUP_LABEL'] = dgettext('rolodex', 'Miscellaneous');
        $tpl['EXPIRATION_GROUP_LABEL'] = dgettext('rolodex', 'Expiration utilities');
        $tpl['SEARCH_GROUP_LABEL'] = dgettext('rolodex', 'Search utilities');
        $tpl['COMMENTS_GROUP_LABEL'] = dgettext('rolodex', 'Comments utilities');

        $tpl['EXPORT_CSV'] = PHPWS_Text::moduleLink(dgettext('rolodex', 'Export records to csv'), 'rolodex', array('uop' => 'export'));

        $tpl['RESET_EXPIRED'] = PHPWS_Text::secureLink(sprintf(dgettext('rolodex', 'Reset all expiry dates to %s days from now'), PHPWS_Settings::get('rolodex', 'expiry_interval')), 'rolodex', array('aop' => 'reset_expired'));
        $vars['aop'] = 'delete_expired';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('rolodex', $vars, true);
        $js['QUESTION'] = dgettext('rolodex', 'Are you sure you want to delete all expired members?\nEach user\'s demographic information will be retained for other modules, but all Rolodex information will be permanently removed.');
        $js['LINK'] = dgettext('rolodex', 'Delete all expired members');
        $tpl['DELETE_EXPIRED'] = javascript('confirm', $js);

        $tpl['SEARCH_INDEX_ALL'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Register all current rolodex records to the search module'), 'rolodex', array('aop' => 'search_index_all'));
        $tpl['SEARCH_REMOVE_ALL'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Remove all current rolodex records from the search module'), 'rolodex', array('aop' => 'search_remove_all'));


        $tpl['ALL_COMMENTS_YES'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Reset "Allow comments" on all records to yes'), 'rolodex', array('aop' => 'all_comments_yes'));
        $tpl['ALL_COMMENTS_NO'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Reset "Allow comments" on all records to no'), 'rolodex', array('aop' => 'all_comments_no'));
        $tpl['ALL_ANON_YES'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Reset "Allow anonymous comments" on all records to yes'), 'rolodex', array('aop' => 'all_anon_yes'));
        $tpl['ALL_ANON_NO'] = PHPWS_Text::secureLink(dgettext('rolodex', 'Reset "Allow anonymous comments" on all records to no'), 'rolodex', array('aop' => 'all_anon_no'));

        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Utilities'), PHPWS_Settings::get('rolodex', 'module_title'));
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'utilities.tpl');
    }

    public function showInfo()
    {

        $filename = 'mod/rolodex/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('rolodex', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('rolodex', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('rolodex', 'If you would like to help out with the ongoing development of Rolodex, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Rolodex%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->rolodex->title = dgettext('rolodex', 'Read me');
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'info.tpl');
    }

    /* not in use */

    public function categories()
    {

        $tpl['ALPHA_CLICK'] = $this->rolodex->alpha_click();
        $tpl['CATLIST'] = Categories::getCategoryList('rolodex');

        $this->rolodex->title = sprintf(dgettext('rolodex', '%s Categories'), PHPWS_Settings::get('rolodex', 'module_title'));
        $this->rolodex->content = PHPWS_Template::process($tpl, 'rolodex', 'categories.tpl');
    }

}

?>