<?php

/**
 * Contains the forms and administrative option for Menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Menu_Admin {

    function main()
    {
        $title = $content = $message = NULL;

        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if (!Current_User::allow('menu')){
            Current_User::disallow(_('User attempted access to Menu administration.'));
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

        case 'pick_link':
            Layout::nakedDisplay(Menu_Admin::pickLink());
            break;

        case 'new':
            $title = _('Create New Menu');
            $content = Menu_Admin::editMenu($menu);
            break;

        case 'delete_menu':
            $menu->kill();
            Menu_Admin::sendMessage(_('Menu deleted.'), 'list');
            break;

        case 'enable_admin_mode':
        case 'disable_admin_mode':
            if ($command == 'enable_admin_mode') {
                $_SESSION['Menu_Admin_Mode'] = TRUE;
            } else {
                unset($_SESSION['Menu_Admin_Mode']);
            }
            if (isset($_REQUEST['return'])) {
                PHPWS_Core::goBack();
            }
        case 'settings':
            $title = _('Menu Settings');
            $content = Menu_Admin::settings();
            break;

        case 'move_link_up':
            $link = new Menu_Link($_REQUEST['link_id']);
            $link->moveUp();
            PHPWS_Core::goBack();
            break;

        case 'move_link_down':
            $link = new Menu_Link($_REQUEST['link_id']);
            $link->moveDown();
            PHPWS_Core::goBack();
            break;

        case 'edit_menu':
            $title = _('Update Menu');
            $content = Menu_Admin::editMenu($menu);
            break;

        case 'edit_link_title':
            $result = Menu_Admin::editLinkTitle($_REQUEST['link_id'], $_REQUEST['link_title']);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Sorry');
                $content = _('A problem occurred when saving your link.');
            } else {
                PHPWS_Core::goBack();
            }
            break;

        case 'edit_link':
            $link = new Menu_Link($_REQUEST['link_id']);
            Menu_Admin::siteLink($menu, $link);
            break;

        case 'delete_link':
            Menu::deleteLink($_REQUEST['link_id']);
            PHPWS_Core::goBack();
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
                PHPWS_Core::goBack();
            }

            if ($result) {
                PHPWS_Core::goBack();
            } else {
                $title = _('Error');
                $content = _('There was a problem saving your link.');
            }
            break;

        case 'add_site_link':
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
                javascript('onload', array('function'=>'opener.window.location.reload(); window.close()'));
                Layout::nakedDisplay('');
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
                $title = _('Create New Menu');
                $content = Menu_Admin::editMenu($menu);
            } else {
                Menu_Admin::sendMessage(_('Menu saved'), 'list');
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

        } // end command switch

        $tpl['TITLE']   = $title;
        $tpl['CONTENT'] = $content;
        $tpl['MESSAGE'] = $message;

        $final_content = PHPWS_Template::process($tpl, 'menu', 'main.tpl');
        $panel->setContent($final_content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    function addPinLink()
    {
        $pin_id = &$_POST['links'];
        $link_id = &$_POST['link_id'];
        @$pin = $_SESSION['Menu_Pin_Links'][$pin_id];
        if (empty($pin)) {
            return false;
        }


        $link = new Menu_Link;
        $link->menu_id = (int)$_POST['menu_id'];
        $link->title   = $pin['title'];
        $link->url     = $pin['url'];
        $link->key_id  = 0;
        if ($link_id) {
            $link->parent = (int)$link_id;
        }

        $result = $link->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }

        unset($_SESSION['Menu_Pin_Links'][$pin_id]);
        if (empty($_SESSION['Menu_Pin_Links'])) {
            unset($_SESSION['Menu_Pin_Links']);
        }
    }

    function sendMessage($message, $command)
    {
        $_SESSION['Menu_message'] = $message;
        PHPWS_Core::reroute(sprintf('index.php?module=menu&command=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    function pinMenu()
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

    function unpinMenu(&$menu)
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

    function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Menu_message'])) {
            $message = $_SESSION['Menu_message'];
        }
        unset($_SESSION['Menu_message']);
        return $message;
    }

    function addLink(&$menu, $key_id, $parent=0)
    {
        $result = $menu->addLink($key_id, $parent);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        return TRUE;
    }


    function addRawLink(&$menu, $title, $url, $parent=0)
    {
        $result = $menu->addRawLink($title, $url, $parent);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        return TRUE;
    }


    function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        if (Current_User::allow('menu', 'create_new_menu')) {
            $newLink = 'index.php?module=menu';
            $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
            $tabs['new'] = $newCommand;
        }
        
        $listLink = 'index.php?module=menu';
        $listCommand = array ('title'=>_('List'), 'link'=> $listLink);
        $tabs['list'] = $listCommand;

        $adminCommand = array('title' => _('Settings'), 'link' => 'index.php?module=menu');
        $tabs['settings'] = $adminCommand;

        $panel = new PHPWS_Panel('menu');
        $panel->quickSetTabs($tabs);

        $panel->setModule('menu');
        return $panel;
    }

    function editMenu(&$menu)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'post_menu');
        if ($menu->id) {
            $form->addHidden('menu_id', $menu->id);
            $form->addSubmit('submit', _('Update'));
        } else {
            $form->addSubmit('submit', _('Create'));
        }

        $form->addCheck('pin_all', 1);
        $form->setMatch('pin_all', $menu->pin_all);
        $form->setLabel('pin_all', _('Pin to all pages'));

        $form->addText('title', $menu->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 30, 30);

        if($template_list = $menu->getTemplateList()) {
            $form->addSelect('template', $template_list);
            $form->setMatch('template', $menu->template);
            $form->setLabel('template', _('Template'));
        } else {
            $form->addTplTag('TEMPLATE_LABEL', _('Template'));
            $form->addTplTag('TEMPLATE', _('Cannot locate any menu templates. Cannot continue.'));
            $form->dropElement('submit');
        }

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'menu', 'menu_form.tpl');
    }


    function editLinkTitle($link_id, $title)
    {
        if (empty($title)) {
            return TRUE;
        }

        $link = new Menu_Link($link_id);
        if (empty($link->_error)) {
            $link->setTitle($title);
            return $link->save();
        } else {
            return $link->_error;
        }
    }

    function menuList()
    {
        $page_tags['TITLE'] = _('Title');
        $page_tags['ACTION'] = _('Action');

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('menus', 'Menu_Item');
        $pager->setModule('menu');
        $pager->addPageTags($page_tags);
        $pager->setTemplate('admin/menu_list.tpl');
        $pager->setLink('index.php?module=menu&amp;tab=list');
        $pager->addRowTags('getRowTags');
        $content = $pager->get();
        return $content;
    }

    function pickLink()
    {
        $menu_id = (int)$_GET['menu_id'];
        if (isset($_GET['link_id'])) {
            $link_id = (int)$_GET['link_id'];
        } else {
            $link_id = 0;
        }

        if (!isset($_SESSION['Menu_Pin_Links'])) {
            return _('No links in queue.');
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
        $form->setLabel('links', _('Pinned links'));
        $form->addSubmit('add', _('Add to menu'));

        $tpl = $form->getTemplate();
        $tpl['CLOSE'] = sprintf('<a href="#" onclick="window.close(); return false">%s</a>', _('Close'));
        return PHPWS_Template::process($tpl, 'menu', 'admin/pin_list.tpl');
    }


    function settings()
    {
        if (!isset($_SESSION['Menu_Admin_Mode'])) {
            $vars['command'] = 'enable_admin_mode';
            $tpl['ADMIN_LINK'] = PHPWS_Text::secureLink(_('Enable Administration Mode'),
                                                        'menu', $vars);
        } else {
            $vars['command'] = 'disable_admin_mode';
            $tpl['ADMIN_LINK'] = PHPWS_Text::secureLink(_('Disable Administration Mode'),
                                                        'menu', $vars);
        }
        return PHPWS_Template::process($tpl, 'menu', 'admin/settings.tpl');
    }

    function siteLink($menu, $link, $errors=NULL)
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
        $form->setLabel('title', _('Title'));
        if (MENU_TITLE_LIMIT > 0) {
            $form->setSize('title', MENU_TITLE_LIMIT);
            $form->setMaxSize('title', MENU_TITLE_LIMIT);
        }

        $form->addText('url', $link->url);
        $form->setLabel('url', _('Url'));
        $form->setSize('url', 50);
        
        $form->addSubmit(_('Save link'));

        $template = $form->getTemplate();

        $template['FORM_TITLE'] = _('Create Link');
        $template['CANCEL'] = javascript('close_window');

        if ($errors) {
            $template['ERRORS'] = implode('<br />', $errors);
        }

        $content = PHPWS_Template::process($template, 'menu', 'admin/offsite.tpl');
        Layout::nakedDisplay($content);
    }

    function postSiteLink(&$link)
    {
        if (empty($_POST['title'])) {
            $error[] = _('Missing title.');
        } else {
            $link->setTitle($_POST['title']);
        }

        if (empty($_POST['url'])) {
            $error[] = _('Missing url.');
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
            return TRUE;
        }
    }
    
}

?>