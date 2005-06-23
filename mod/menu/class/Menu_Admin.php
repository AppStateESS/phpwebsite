<?php

/**
 * Contains the forms and administrative option for Menu
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu
 * @version $Id$
 */

class Menu_Admin {

    function main()
    {
        $title = $content = NULL;

        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if (!Current_User::allow('menu')){
            Current_User::disallow(_('User attempted access to Menu administration.'));
            return;
        }

        $panel = & Menu_Admin::cpanel();

        if (isset($_REQUEST['command']))
            $command = $_REQUEST['command'];
        else
            $command = $panel->getCurrentTab();

        if (isset($_REQUEST['menu_id'])) {
            $menu = & new Menu_Item((int)$_REQUEST['menu_id']);
        } else {
            $menu = & new Menu_Item;
        }

        // start command switch
        switch ($command) {
        case 'new':
            $title = _('Create New Menu');
            $content = Menu_Admin::editMenu($menu);
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

        case 'delete_link':
            Menu::deleteLink($_REQUEST['link_id']);
            PHPWS_Core::goBack();
            break;

        case 'list':
            $title = ('Menu List');
            $content = Menu_Admin::menuList();
            break;

        case 'add_link':
            if (!isset($_REQUEST['parent'])) {
                $parent = 0;
            } else {
                $parent = $_REQUEST['parent'];
            }

            $result = Menu_Admin::addLink($menu, $parent);
            if ($result) {
                PHPWS_Core::goBack();
            } else {
                $title = _('Error');
                $content = _('There was a problem saving your link.');
            }
            break;

        case 'edit_links':
            $title = sprintf(_('Edit Links: %s'), $menu->title);
            $content = Menu_Admin::editLinks($menu);
            break;

        case 'post_menu':
            $updating = (bool)$menu->id;
            $post_result = $menu->post();
            if (is_array($post_result)) {
                $tpl['MESSAGE'] = implode('<br />', $post_result);
                $title = _('Create New Menu');
                $content = Menu_Admin::editMenu($menu);
            } else {
                $title = _('Menu saved!');
                if ($updating) {
                    $content = _('Returning you to menu list.');
                    $tab = 'list';
                } else {
                    $content = _('Returning you to menu creation.');
                    $tab = 'new';
                }
                Layout::metaRoute('index.php?module=menu&amp;tab='
                                  . $tab
                                  . '&amp;authkey=' . Current_User::getAuthKey());
            }
            break;

        case 'pin_all':
            $menu->pin_all = (int)$_GET['hook'];
            $menu->save();
            $title = ('Menu List');
            $content = Menu_Admin::menuList();
            break;

        } // end command switch

        $tpl['TITLE'] = $title;
        $tpl['CONTENT'] = $content;

        $final_content = PHPWS_Template::process($tpl, 'menu', 'main.tpl');
        $panel->setContent($final_content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }


    function addLink(&$menu, $parent=0)
    {
        if (empty($_REQUEST['link_title']) || empty($_REQUEST['url'])) {
            return FALSE;
        }

        $title = urldecode($_REQUEST['link_title']);
        $url = urldecode($_REQUEST['url']);

        $result = $menu->addLink($title, $url, $parent);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        return TRUE;
    }


    function &cpanel()
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

        $panel = & new PHPWS_Panel('categories');
        $panel->quickSetTabs($tabs);

        $panel->setModule('menu');
        //    $panel->setPanel('panel.tpl');
        return $panel;
    }

    function editMenu(&$menu)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'menu');
        $form->addHidden('command', 'post_menu');
        if ($menu->id) {
            $form->addHidden('menu_id', $menu->id);
            $form->addSubmit('submit', _('Update'));
        } else {
            $form->addSubmit('submit', _('Create'));
        }

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

    function editLinks(&$menu)
    {

    }

    function editLinkTitle($link_id, $title)
    {
        if (empty($title)) {
            return TRUE;
        }

        $link = & new Menu_Link($link_id);
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
        $pager = & new DBPager('menus', 'Menu_Item');
        $pager->setModule('menu');
        $pager->addPageTags($page_tags);
        $pager->setTemplate('admin/menu_list.tpl');
        $pager->setLink('index.php?module=menu&amp;tab=list');
        $pager->addRowTags('getRowTags');
        $content = $pager->get();
        return $content;
    }

}

?>