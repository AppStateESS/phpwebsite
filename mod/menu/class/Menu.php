<?php
/**
 * Main functionality class for Menu module
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu
 * @version $Id$
 */

class Menu {

    function admin()
    {
        PHPWS_Core::initModClass('menu', 'Menu_Admin.php');
        Menu_Admin::main();
    }

    function showPinned()
    {
        Layout::addStyle('menu');
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');
        $db = & new PHPWS_DB('menus');
        $db->addWhere('pin_all', 1);
        $result = $db->getObjects('Menu_Item');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return;
        }

        $GLOBALS['Pinned_Menus'] = $result;

        foreach ($result as $menu) {
            $menu->view();
        }
    }

    function show($key, $title=NULL, $url=NULL)
    {
        Layout::addStyle('menu');
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');
        if (!empty($title) || !empty($url)) {
            Menu::readyLink($title, $url);
        }

        $db = & new PHPWS_DB('menus');
        $db->addWhere('menu_assoc.module', $key->getModule());
        $db->addWhere('menu_assoc.item_name', $key->getItemName());
        $db->addWhere('menu_assoc.item_id', $key->getItemId());
        $db->addWhere('id', 'menu_assoc.id');
        $db->addWhere('pin_all', 0);
        $result = $db->getObjects('menu_item');

        if (empty($result) || PEAR::isError($result)) {
            return $result;
        }

        foreach ($result as $menu) {
            $menu->view();
        }
    }

    function atLink($url)
    {
        $compare = Menu::grabUrl();
        return $url == $compare;
    }

    function getAddLink($menu_id, $parent_id=NULL)
    {
        PHPWS_Core::configRequireOnce('menu', 'config.php');
        if (isset($_REQUEST['authkey'])) {
            return NULL;
        }
        $direct_link = FALSE;

        if (empty($GLOBALS['Menu_Ready_Link'])) {
            $title = NULL;
            $url = Menu::grabUrl();
        } else {
            if (empty($GLOBALS['Menu_Ready_Link']['title'])) {
                $title = NULL;
            } else {
                $title = $GLOBALS['Menu_Ready_Link']['title'];
            }

            if (!empty($GLOBALS['Menu_Ready_Link']['url'])) {
                $url = str_replace('&amp;', '&', $GLOBALS['Menu_Ready_Link']['url']);
            } else {
                $url = Menu::grabUrl();
            }
        }

        $vars['command'] = 'add_link';
        $vars['menu_id'] = $menu_id;
        if (!empty($parent_id)) {
            $vars['parent'] = $parent_id;
        } else {
            $vars['parent'] = 0;
        }


        if (!empty($title)) {
            $vars['title'] = urlencode($title);
            $direct_link = TRUE;
        }
    
        if (!empty($url)) {
            $vars['url'] = urlencode(urlencode($url));
        }

        if ($direct_link) {
            return PHPWS_Text::secureLink(MENU_LINK_ADD, 'menu', $vars);
        } else {
            $js['question']   = _('Enter link title');
            $js['address']    = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $js['link']       = MENU_LINK_ADD;
            $js['value_name'] = 'title';
            return javascript('prompt', $js);
        }
    }

    function grabUrl()
    {
        static $url = NULL;

        if (!empty($url)) {
            return $url;
        }

        $get_values = PHPWS_Text::getGetValues();
        if (!empty($get_values)) {
            unset($get_values['authkey']);
        } else {
            return 'index.php';
        }

        foreach ($get_values as $key => $value) {
            $new_link[] = "$key=$value";
        }

        return  'index.php?' . implode('&', $new_link);
    }

    function deleteLink($link_id)
    {
        $link = & new Menu_Link($link_id);
        return $link->delete();
    }

    function readyLink($title=NULL, $url=NULL)
    {
        $GLOBALS['Menu_Ready_Link']['title'] = $title;
        $GLOBALS['Menu_Ready_Link']['url']   = $url;
    }

    function isAdminMode()
    {
        return TRUE;
    }

}

?>
