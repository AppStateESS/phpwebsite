<?php

/**
 * Contains the forms and administrative option for Menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Menu_Admin {

    public function main()
    {
        $title = $content = $message = NULL;

        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if (!Current_User::allow('menu')){
            Current_User::disallow(dgettext('menu', 'User attempted access to Menu administration.'));
            return;
        }

        $panel = Menu_Admin::cpanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['menu_id'])) {
            $menu = new Menu_Item((int)$_REQUEST['menu_id']);
        } else {
            $menu = new Menu_Item;
        }

        // start command switch
        switch ($command) {

        case 'add_pin_link':
            Menu_Admin::addPinLink();
            javascript('close_refresh');
            Layout::nakedDisplay();
            break;

        case 'popup_admin':
            Layout::nakedDisplay(Menu_Admin::popupLinkAdmin());
            break;

        case 'pin_page_post':
            if (empty($_POST['title'])) {
                Menu_Admin::pinPageForm($_POST['url'], true);
            } else {
                Menu::pinLink($_POST['title'], $_POST['url']);
                javascript('close_refresh');
                Layout::nakedDisplay();
            }
            break;

        case 'pick_link':
            if (count($_SESSION['Menu_Pin_Links']) < 2) {
                Menu_Admin::quickPinLink($_GET['menu_id']);
                Layout::nakedDisplay(javascript('close_refresh'));
            } else {
                Layout::nakedDisplay(Menu_Admin::pickLink());
            }
            break;

        case 'new':
            $title = dgettext('menu', 'Create New Menu');
            $content = Menu_Admin::editMenu($menu);
            break;

        case 'delete_menu':
            $menu->kill();
            Menu_Admin::sendMessage(dgettext('menu', 'Menu deleted.'), 'list');
            break;

        case 'enable_admin_mode':
        case 'disable_admin_mode':
            if ($command == 'enable_admin_mode') {
                $_SESSION['Menu_Admin_Mode'] = true;
            } else {
                $_SESSION['Menu_Admin_Mode'] = false;
                unset($_SESSION['Menu_Admin_Mode']);
            }
            if (isset($_REQUEST['return'])) {
                PHPWS_Core::goBack();
            }
        case 'settings':
            $title = dgettext('menu', 'Menu Settings');
            $content = Menu_Admin::settings();
            break;

        case 'move_link':
            if (empty($_GET['key_id'])) {
                $key = Key::getHomeKey();
            } else {
                $key = new Key($_GET['key_id']);
            }
            $key->flag();

            $link = new Menu_Link($_GET['link_id']);
            if ($_GET['dir'] == 'up') {
                $link->moveUp();
            } else {
                $link->moveDown();
            }
            echo $menu->view(false, true, $key);
            exit();

        case 'move_link_up':
            $link = new Menu_Link($_REQUEST['link_id']);
            $link->moveUp();
            Menu_Admin::finish();
            break;

        case 'move_link_down':
            $link = new Menu_Link($_REQUEST['link_id']);
            $link->moveDown();
            Menu_Admin::finish();
            break;

        case 'edit_menu':
            $title = dgettext('menu', 'Update Menu');
            $content = Menu_Admin::editMenu($menu);
            break;

        case 'edit_link_title':
            $result = Menu_Admin::editLinkTitle($_REQUEST['link_id'], $_REQUEST['link_title']);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = dgettext('menu', 'Sorry');
                $content = dgettext('menu', 'A problem occurred when saving your link.');
            } else {
                Menu_Admin::finish();
            }
            break;

        case 'edit_link':
            $link = new Menu_Link($_REQUEST['link_id']);
            Menu_Admin::siteLink($menu, $link);
            break;

        case 'delete_link':
            Menu::deleteLink($_REQUEST['link_id']);

            if (isset($_GET['ajax'])) {
                if (empty($_GET['key_id'])) {
                    $key = Key::getHomeKey();
                } else {
                    $key = new Key($_GET['key_id']);
                }
                $key->flag();

                echo $menu->view(false, true, $key);
                exit();
            } else {
                Menu_Admin::finish();
            }
            break;

        case 'list':
            $panel->setCurrentTab('list');
            $title = ('Menu List');
            $content = Menu_Admin::menuList();
            break;

        case 'unclip':
            unset($_SESSION['Menu_Clip'][$_GET['menu_id']]);
            PHPWS_Core::goBack();
            break;

        case 'clip':
            $_SESSION['Menu_Clip'][$_GET['menu_id']] = $_GET['menu_id'];
            PHPWS_Core::goBack();
            break;

        case 'ajax_add_link':
            $parent_id = (int)$_GET['parent'];

            if (empty($_GET['key_id']) && empty($_GET['ref_key'])) {
                $key = Key::getHomeKey();
            } elseif (isset($_GET['key_id'])) {
                $key = new Key($_GET['key_id']);
            } else {
                $key = new Key($_GET['ref_key']);
            }
            $key->flag();
            

            if (isset($_GET['key_id'])) {
                $result = Menu_Admin::addLink($menu, $_GET['key_id'], $parent_id);
            } elseif (isset($_REQUEST['url'])) {
                $key = Key::getHomeKey();
                $result = Menu_Admin::addRawLink($menu, $_GET['link_title'], $_GET['url'], $parent_id);
            }
            echo $menu->view(false, true, $key);
            exit();

            break;

        case 'add_link':
            if (!isset($_REQUEST['parent'])) {
                $parent_id = 0;
            } else {
                $parent_id = $_REQUEST['parent'];
            }

            if (isset($_REQUEST['key_id'])) {
                $result = Menu_Admin::addLink($menu, $_REQUEST['key_id'], $parent_id);
            } elseif (isset($_REQUEST['url'])) {
                $result = Menu_Admin::addRawLink($menu, $_REQUEST['link_title'], $_REQUEST['url'], $parent_id);
            } else {
                Menu_Admin::finish();
            }

            if ($result) {
                Menu_Admin::finish();
            } else {
                $title = dgettext('menu', 'Error');
                $content = dgettext('menu', 'There was a problem saving your link.');
            }
            break;

        case 'add_site_link':
            $script = '<script type="text/javascript">window.resizeTo(500,300);</script>';
            Layout::addJSHeader($script,'resize');
            $link = new Menu_Link;
            $link->parent = $_REQUEST['parent_id'];
            if (isset($_REQUEST['dadd'])) {
                $link->url = $_REQUEST['dadd'];
            }
            Menu_Admin::siteLink($menu, $link);
            break;

        case 'edit_site_link':
            $link = new Menu_Link($_REQUEST['link_id']);
            Menu_Admin::siteLink($menu, $link);
            break;

        case 'pin_page':
            Menu_Admin::pinPage();
            break;

        case 'post_site_link':
            if (isset($_REQUEST['link_id'])) {
                $link = new Menu_Link($_REQUEST['link_id']);
            } else {
                $link = new Menu_Link;
            }

            $result = Menu_Admin::postSiteLink($link);
            if (is_array($result)) {
                Menu_Admin::siteLink($menu, $link, $result);
            } else {
                $link->save();
                Layout::nakedDisplay(javascript('close_refresh'));
            }
            break;

        case 'post_menu':
            if (!Current_User::authorized('menu')) {
                Current_User::disallow();
                return;
            }
            $updating = (bool)$menu->id;
            $post_result = $menu->post();
            if (is_array($post_result)) {
                $tpl['MESSAGE'] = implode('<br />', $post_result);
                $title = dgettext('menu', 'Create New Menu');
                $content = Menu_Admin::editMenu($menu);
            } else {
                Menu_Admin::sendMessage(dgettext('menu', 'Menu saved'), 'list');
            }
            break;

        case 'unpin_menu':
            Menu_Admin::unpinMenu($menu);
            PHPWS_Core::goBack();
            break;

        case 'pin_menu':
            Menu_Admin::pinMenu();
            PHPWS_Core::goBack();
            break;

        case 'pin_all':
            $menu->pin_all = (int)$_GET['hook'];
            $menu->save();
            $title = ('Menu List');
            $content = Menu_Admin::menuList();
            break;

        case 'save_settings':
            Menu_Admin::saveSettings();
            $message = dgettext('menu', 'Settings updated.');
            $title = dgettext('menu', 'Menu Settings');
            $content = Menu_Admin::settings();
            break;

        case 'reorder_links':
            if (!empty($_GET['menu_id'])) {
                $menu->reorderLinks();
            }
            PHPWS_Core::goBack();
            break;
        } // end command switch

        $tpl['TITLE']   = $title;
        $tpl['CONTENT'] = $content;
        $tpl['MESSAGE'] = $message;

        $final_content = PHPWS_Template::process($tpl, 'menu', 'main.tpl');
        $panel->setContent($final_content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    public function addPinLink()
    {
        $pin_id = &$_POST['links'];
        $link_id = &$_POST['link_id'];
        @$pin = $_SESSION['Menu_Pin_Links'][$pin_id];

        if (empty($pin)) {
            return false;
        }

        if (!isset($_POST['remove'])) {
            $link = new Menu_Link;
            $link->menu_id = (int)$_POST['menu_id'];
            $link->title   = $pin['title'];
            $link->url     = $pin['url'];
            if (isset($pin['key_id'])) {
                $link->key_id  = (int)$pin['key_id'];
            } else {
                $link->key_id  = 0;
            }
            if ($link_id) {
                $link->parent = (int)$link_id;
            }

            $result = $link->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
        }
        unset($_SESSION['Menu_Pin_Links'][$pin_id]);
        if (empty($_SESSION['Menu_Pin_Links'])) {
            unset($_SESSION['Menu_Pin_Links']);
        }
    }

    public function sendMessage($message, $command)
    {
        $_SESSION['Menu_message'] = $message;
        PHPWS_Core::reroute(sprintf('index.php?module=menu&command=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    public function pinPageForm($url, $error=false)
    {
        $form = new PHPWS_Form('menu');
        $form->addText('title');
        $form->setLabel('title', dgettext('menu', 'Enter link title'));
        $form->addHidden('command', 'pin_page_post');
        $form->addHidden('url', $url);
        $form->addHidden('module', 'menu');
        $form->addSubmit(dgettext('menu', 'Save'));
        $tpl = $form->getTemplate();
        $tpl['CANCEL'] = javascript('close_window', array('value'=>dgettext('menu', 'Cancel')));
        if ($error) {
            $tpl['ERRORS'] = dgettext('menu', 'You must enter a link title.');
        }
        return PHPWS_Template::process($tpl, 'menu', 'admin/offsite.tpl');
    }

    public function pinPage()
    {
        if (isset($_GET['key_id'])) {
            $key = new Key($_GET['key_id']);
            if ($key) {
                Menu::pinLink($key->title, $key->url, $key->id);
                $content = javascript('close_refresh');
            } else {
                $content = javascript('close_refresh', array('refresh'=>0));
            }
        } elseif (isset($_GET['lurl'])) {
            if (isset($_GET['ltitle'])) {
                Menu::pinLink($_GET['ltitle'], $_GET['lurl']);
                $content = javascript('close_refresh');
            } else {
                $content = Menu_Admin::pinPageForm($_GET['lurl']);
            }
        } else {
            $content = javascript('close_refresh', array('refresh'=>0));
        }
        Layout::nakedDisplay($content);
    }

    public function pinMenu()
    {
        if (!isset($_REQUEST['key_id']) || !isset($_REQUEST['menu_id'])) {
            return;
        }

        $menu_id = &$_REQUEST['menu_id'];
        $key_id = &$_REQUEST['key_id'];

        $db = new PHPWS_DB('menu_assoc');
        $db->addWhere('menu_id', $menu_id);
        $db->addWhere('key_id', $key_id);
        $db->delete();

        $db->addValue('menu_id', $menu_id);
        $db->addValue('key_id', $key_id);
        return $db->insert();
    }

    public function unpinMenu(Menu_Item $menu)
    {
        if (!isset($_REQUEST['key_id']) || !isset($_REQUEST['pin_all'])) {
            return;
        }

        if ($_REQUEST['pin_all']) {
            $menu->pin_all = 0;
            return $menu->save();
        } else {
            $db = new PHPWS_DB('menu_assoc');
            $db->addWhere('menu_id', $menu->id);
            $db->addWhere('key_id', $_REQUEST['key_id']);
            return $db->delete();
        }

    }

    public function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Menu_message'])) {
            $message = $_SESSION['Menu_message'];
        }
        unset($_SESSION['Menu_message']);
        return $message;
    }

    public function addLink(Menu_Item $menu, $key_id, $parent=0)
    {
        $result = $menu->addLink($key_id, $parent);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        return true;
    }


    public function addRawLink(Menu_Item $menu, $title, $url, $parent=0)
    {
        $result = $menu->addRawLink($title, $url, $parent);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        return true;
    }


    public function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        if (Current_User::allow('menu', 'create_new_menu')) {
            $newLink = 'index.php?module=menu';
            $newCommand = array ('title'=>dgettext('menu', 'New'), 'link'=> $newLink);
            $tabs['new'] = $newCommand;
        }

        $listLink = 'index.php?module=menu';
        $listCommand = array ('title'=>dgettext('menu', 'List'), 'link'=> $listLink);
        $tabs['list'] = $listCommand;

        $adminCommand = array('title' => dgettext('menu', 'Settings'), 'link' => 'index.php?module=menu');
        $tabs['settings'] = $adminCommand;

        $panel = new PHPWS_Panel('menu');
        $panel->quickSetTabs($tabs);

        $panel->setModule('menu');
        return $panel;
    }

    public function editMenu(Menu_Item $menu)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'post_menu');
        if ($menu->id) {
            $form->addHidden('menu_id', $menu->id);
            $form->addSubmit('submit', dgettext('menu', 'Update'));
        } else {
            $form->addSubmit('submit', dgettext('menu', 'Create'));
        }

        $form->addCheck('pin_all', 1);
        $form->setMatch('pin_all', $menu->pin_all);
        $form->setLabel('pin_all', dgettext('menu', 'Pin to all pages'));

        $form->addText('title', $menu->title);
        $form->setLabel('title', dgettext('menu', 'Title'));
        $form->setSize('title', 30, 30);

        if($template_list = $menu->getTemplateList()) {
            $form->addSelect('template', $template_list);
            $form->setMatch('template', $menu->template);
            $form->setLabel('template', dgettext('menu', 'Template'));
        } else {
            $form->addTplTag('TEMPLATE_LABEL', dgettext('menu', 'Template'));
            $form->addTplTag('TEMPLATE', dgettext('menu', 'Cannot locate any menu templates. Cannot continue.'));
            $form->dropElement('submit');
        }

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'menu', 'menu_form.tpl');
    }


    public function editLinkTitle($link_id, $title)
    {
        if (empty($title)) {
            return true;
        }

        $link = new Menu_Link($link_id);
        if (empty($link->_error)) {
            $link->setTitle($title);
            return $link->save();
        } else {
            return $link->_error;
        }
    }

    public function menuList()
    {
        $page_tags['ACTION'] = dgettext('menu', 'Action');

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('menus', 'Menu_Item');
        $pager->setModule('menu');
        $pager->addPageTags($page_tags);
        $pager->setTemplate('admin/menu_list.tpl');
        $pager->setLink('index.php?module=menu&amp;tab=list');
        $pager->addRowTags('getRowTags');
        $pager->addSortHeader('title', dgettext('menu', 'Title'));
        $pager->setEmptyMessage(dgettext('menu', 'No menus found. Click on "New" to create one.'));
        $content = $pager->get();
        return $content;
    }

    public function pickLink()
    {
        $menu_id = (int)$_GET['menu_id'];
        if (isset($_GET['link_id'])) {
            $link_id = (int)$_GET['link_id'];
        } else {
            $link_id = 0;
        }

        if (!isset($_SESSION['Menu_Pin_Links'])) {
            return dgettext('menu', 'No links in queue.');
        }

        foreach ($_SESSION['Menu_Pin_Links'] as $key=>$data) {
            $pin_list[$key] = $data['title'];
        }

        $form = new PHPWS_Form('pick_link');
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'add_pin_link');
        $form->addHidden('menu_id', $menu_id);
        $form->addHidden('link_id', $link_id);
        $form->addSelect('links', $pin_list);
        $form->setLabel('links', dgettext('menu', 'Pinned links'));
        $form->addSubmit('add', dgettext('menu', 'Add to menu'));
        $form->addSubmit('remove', dgettext('menu', 'Clear from list'));

        $tpl = $form->getTemplate();
        $tpl['CLOSE'] = sprintf('<a href="#" onclick="window.close(); return false">%s</a>', dgettext('menu', 'Close'));
        return PHPWS_Template::process($tpl, 'menu', 'admin/pin_list.tpl');
    }


    public function settings()
    {
        $form = new PHPWS_Form('menu-settings');
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'save_settings');

        $form->addRadio('admin_mode', array('on', 'off'));
        $form->setLabel('admin_mode', array(dgettext('menu', 'On'), dgettext('menu', 'Off')));

        if (isset($_SESSION['Menu_Admin_Mode']) && $_SESSION['Menu_Admin_Mode'] == true) {
            $form->setMatch('admin_mode', 'on');
        } else {
            $form->setMatch('admin_mode', 'off');
        }

        $form->addCheck('float_mode', 1);
        $form->setLabel('float_mode', dgettext('menu', 'Use floating admin links'));
        $form->setMatch('float_mode', PHPWS_Settings::get('menu', 'float_mode'));

        $form->addCheck('miniadmin', 1);
        $form->setLabel('miniadmin', dgettext('menu', 'Use MiniAdmin instead of Admin mode link'));
        $form->setMatch('miniadmin', PHPWS_Settings::get('menu', 'miniadmin'));

        $form->addCheck('home_link', 1);
        $form->setLabel('home_link', dgettext('menu', 'Add home link on new menus'));
        $form->setMatch('home_link', PHPWS_Settings::get('menu', 'home_link'));

        $form->addText('max_link_characters', PHPWS_Settings::get('menu', 'max_link_characters'));
        $form->setLabel('max_link_characters', dgettext('menu', 'Maximum link characters'));
        $form->setSize('max_link_characters', 3, 3);

        $form->addSubmit('submit', dgettext('menu', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['ADMIN_MODE_NOTE'] = dgettext('menu', 'Admin mode status');

        return PHPWS_Template::process($tpl, 'menu', 'admin/settings.tpl');
    }

    public function siteLink($menu, $link, $errors=NULL)
    {
        $form = new PHPWS_Form('site_link');
        if ($link->id) {
            $form->addHidden('link_id', $link->id);
        }
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'post_site_link');
        $form->addHidden('menu_id', $menu->id);
        $form->addHidden('parent_id', $link->parent);
        $form->addText('title', $link->title);
        $form->setLabel('title', dgettext('menu', 'Title'));
        $char_limit = PHPWS_Settings::get('menu', 'max_link_characters');
        if ($char_limit > 0) {
            $form->setSize('title', $char_limit);
            $form->setMaxSize('title', $char_limit);
        }

        $form->addText('url', $link->url);
        $form->setLabel('url', dgettext('menu', 'Url'));
        $form->setSize('url', 50);

        $form->addSubmit(dgettext('menu', 'Save link'));

        $template = $form->getTemplate();

        if ($link->id) {
            $template['FORM_TITLE'] = dgettext('menu', 'Edit Link');
        } else {
            $template['FORM_TITLE'] = dgettext('menu', 'Create Link');
        }
        $template['CANCEL'] = javascript('close_window');

        if ($errors) {
            $template['ERRORS'] = implode('<br />', $errors);
        }

        $content = PHPWS_Template::process($template, 'menu', 'admin/offsite.tpl');
        Layout::addJSHeader('<script type="text/javascript">self.resizeTo(500,300);</script>');
        Layout::nakedDisplay($content);
    }

    public function postSiteLink(Menu_Link $link)
    {
        if (empty($_POST['title'])) {
            $error[] = dgettext('menu', 'Missing title.');
        } else {
            $link->setTitle($_POST['title']);
        }

        if (empty($_POST['url'])) {
            $error[] = dgettext('menu', 'Missing url.');
        } else {
            $link->setUrl($_POST['url']);
        }

        $link->key_id = 0;
        if (!$link->menu_id) {
            $link->menu_id =  $_POST['menu_id'];
        }

        if (!$link->parent) {
            $link->parent = $_POST['parent_id'];
        }

        if (isset($error)) {
            return $error;
        } else {
            return true;
        }
    }

    public function quickPinLink()
    {
        if (empty($_SESSION['Menu_Pin_Links'])) {
            return;
        }
        $link = new Menu_Link;
        $link->menu_id = $_GET['menu_id'];
        foreach ($_SESSION['Menu_Pin_Links'] as $pin);
        $link->setTitle($pin['title']);
        $link->setUrl($pin['url']);

        if (isset($pin['key_id'])) {
            $link->key_id  = (int)$pin['key_id'];
        } else {
            $link->key_id  = 0;
        }
        if (!empty($_GET['link_id'])) {
            $link->parent = (int)$_GET['link_id'];
        }

        $link->save();
        unset($_SESSION['Menu_Pin_Links']);
    }

    public function popupLinkAdmin()
    {
        $link = new Menu_Link($_GET['link_id']);

        if (isset($_GET['key_id'])) {
            if ($_GET['key_id']) {
                $key = new Key($_GET['key_id']);
            } else {
                $key = Key::getHomeKey();
            }
            $key->flag();
        }

        $template = array();
        $link->_loadAdminLinks($template, true);

        $template['CLOSE'] = javascript('close_window');

        $content = PHPWS_Template::process($template, 'menu', 'popup_admin.tpl');

        return $content;
    }

    public function finish()
    {
        if (isset($_GET['pu'])) {
            javascript('close_refresh');
            Layout::nakedDisplay();
        } else {
            PHPWS_Core::goBack();
        }
    }

    public function saveSettings()
    {
        if ($_POST['admin_mode'] == 'on') {
            $_SESSION['Menu_Admin_Mode'] = true;
        } else {
            $_SESSION['Menu_Admin_Mode'] = false;
            unset($_SESSION['Menu_Admin_Mode']);
        }

        if (!empty($_POST['max_link_characters'])) {
            $chars = (int)$_POST['max_link_characters'];
            if ($chars > 10 && $chars < 1000) {
                PHPWS_Settings::set('menu', 'max_link_characters', $chars);
            }
        }

        if (isset($_POST['float_mode'])) {
            PHPWS_Settings::set('menu', 'float_mode', 1);
        } else {
            PHPWS_Settings::set('menu', 'float_mode', 0);
        }

        if (isset($_POST['miniadmin'])) {
            PHPWS_Settings::set('menu', 'miniadmin', 1);
        } else {
            PHPWS_Settings::set('menu', 'miniadmin', 0);
        }

        if (isset($_POST['home_link'])) {
            PHPWS_Settings::set('menu', 'home_link', 1);
        } else {
            PHPWS_Settings::set('menu', 'home_link', 0);
        }

        PHPWS_Settings::save('menu');

    }
}

?>