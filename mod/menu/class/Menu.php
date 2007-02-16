<?php
/**
 * Main functionality class for Menu module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
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
        $db = new PHPWS_DB('menus');
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
        $seen = array();

        $key = Key::getCurrent();
        if (empty($key) || empty($key->title) || empty($key->url)) {
            return;
        }

        Layout::addStyle('menu');
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        $db = new PHPWS_DB('menus');
        $db->addWhere('menu_assoc.key_id', $key->id);
        $db->addWhere('id', 'menu_assoc.menu_id');
        $result = $db->getObjects('Menu_Item');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        } elseif (!empty($result)) {
            foreach ($result as $menu) {
                $seen[] = $menu->id;
                $menu->view();
            }
        }

        if (isset($_SESSION['Menu_Clip'])) {
            foreach ($_SESSION['Menu_Clip'] as $menu_id) {
                if (in_array($menu_id, $seen)) {
                    continue;
                }
                $menu = new Menu_Item($menu_id);
                $menu->view(true);
            }
        }

    }

    function atLink($url)
    {
        $compare =  PHPWS_Core::getCurrentUrl();
        return $url == $compare;
    }

    function getSiteLink($menu_id, $parent_id=0, $isKeyed=false)
    {
        $vars['command']   = 'add_site_link';
        $vars['menu_id']   = $menu_id;
        $vars['parent_id'] = $parent_id;

        if (!$isKeyed) {
            $vars['dadd'] = urlencode(PHPWS_Core::getCurrentUrl(false));
        }
        translate('menu');
        $js['link_title'] = _('Add Other Link');
        $js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE, FALSE);
        $js['label'] = MENU_LINK_ADD_SITE;
        $js['width'] = 500;
        $js['height'] = 200;
        translate();
        return javascript('open_window', $js);
    }

    function getAddLink($menu_id, $parent_id=NULL)
    {
        $key = Key::getCurrent();
        if (empty($key->url)) {
            return NULL;
        }

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

        if ($key->id) {
            $vars['key_id'] = $key->id;
            return PHPWS_Text::secureLink(MENU_LINK_ADD, 'menu', $vars);
        } else {
            // for dummy keys
            if (empty($key->title)) {
                $vars['url']      = urlencode($key->url);
                translate('menu');
                $js['question']   = _('Enter link title');
                translate();
                $js['address']    = PHPWS_Text::linkAddress('menu', $vars, TRUE, FALSE);
                $js['link']       = MENU_LINK_ADD;
                $js['value_name'] = 'link_title';
                return javascript('prompt', $js);
            } else {
                $vars['link_title'] = urlencode($key->title);
                $vars['url']        = urlencode($key->url);
                return PHPWS_Text::secureLink(MENU_LINK_ADD, 'menu', $vars);
            }
        }

    }

    function pinLink($title, $url)
    {
        $key = substr(md5($title), 0, 8);
        $_SESSION['Menu_Pin_Links'][$key]['title'] = $title;
        $_SESSION['Menu_Pin_Links'][$key]['url'] = $url;
    }

    function getUnpinLink($menu_id, $key_id, $pin_all=0)
    {
        $vars['command'] = 'unpin_menu';
        $vars['menu_id'] = $menu_id;
        if ($key_id >= 0) {
            $vars['key_id'] = $key_id;
        }
        $vars['pin_all'] = $pin_all;
        translate('menu');
        if ($pin_all) {
            $js['QUESTION']   = _('Are you sure you want to unpin this menu from all pages?');
        } else {
            $js['QUESTION']   = _('Are you sure you want to unpin this menu from this page?');
        }
        $js['ADDRESS']    = PHPWS_Text::linkAddress('menu', $vars, TRUE);
        $js['LINK']       = MENU_UNPIN;
        translate();
        return javascript('confirm', $js);
    }

    function deleteLink($link_id)
    {
        $link = new Menu_Link($link_id);
        return $link->delete();
    }


    function isAdminMode()
    {
        return isset($_SESSION['Menu_Admin_Mode']);
    }

    function siteMap()
    {
        if (!isset($_GET['site_map'])) {
            PHPWS_Core::errorPage('404');
        }
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');
        $menu = new Menu_Item((int)$_GET['site_map']);
        if (empty($menu->title)) {
            PHPWS_Core::errorPage('404');
        }

        $result = $menu->getLinks();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            PHPWS_Core::errorPage();
        }
        $content = array();
        if (!empty($result)) {
            Menu::walkLinks($result, $content);
        }
        translate('menu');
        $tpl['TITLE'] = $menu->getTitle() . ' - ' . _('Site map');
        $tpl['CONTENT'] = implode('', $content);
        translate();
        Layout::add(PHPWS_Template::process($tpl, 'menu', 'site_map.tpl'));
    }

    function walkLinks($links, &$content)
    {
        $content[] = '<ol>';
        foreach ($links as $link) {
            $content[] = '<li>';
            $content[] = $link->getUrl();
            if (!empty($link->_children)) {
                Menu::walkLinks($link->_children, $content);
            }
            $content[] = '</li>';
        }
        $content[] = '</ol>';
    }

}

?>
