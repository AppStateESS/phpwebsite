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
    
    /**
     * Grabs all the menus pinned to every page and displays
     * them.
     */
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

        if (empty($result)) {
            return NULL;
        }

        $GLOBALS['Pinned_Menus'] = $result;

        foreach ($result as $menu) {
            $menu->view();
        }
    }
    
    /**
     * Function called by mod developer to add their
     * link or to just show the menu on that item
     */
    function show()
    {
        $key = Key::getCurrent();
        if (empty($key) || empty($key->title) || empty($key->url)) {
            return;
        }

        Layout::addStyle('menu');
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        $db = & new PHPWS_DB('menus');
        $db->addWhere('menu_assoc.key_id', $key->id);
        $db->addWhere('id', 'menu_assoc.menu_id');

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
        $compare =  PHPWS_Core::getCurrentUrl();
        return $url == $compare;
    }

    function getAddLink($menu_id, $parent_id=NULL)
    {
        PHPWS_Core::configRequireOnce('menu', 'config.php');
        $key = Key::getCurrent();

        if (empty($key)) {
            return NULL;
        }

        $vars['command'] = 'add_link';
        $vars['menu_id'] = $menu_id;
        if (!empty($parent_id)) {
            $vars['parent'] = $parent_id;
        } else {
            $vars['parent'] = 0;
        }
        $vars['key_id'] = $key->id;

        if (!empty($key->title)) {
            return PHPWS_Text::secureLink(MENU_LINK_ADD, 'menu', $vars);
        } else {
            $js['question']   = _('Enter link title');
            $js['address']    = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $js['link']       = MENU_LINK_ADD;
            $js['value_name'] = 'link_title';
            return javascript('prompt', $js);
        }
    }

    function deleteLink($link_id)
    {
        $link = & new Menu_Link($link_id);
        return $link->delete();
    }


    function isAdminMode()
    {
        return isset($_SESSION['Menu_Admin_Mode']);
    }

}

?>
