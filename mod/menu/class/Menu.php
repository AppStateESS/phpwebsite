<?php
/**
 * Main functionality class for Menu module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('menu', 'Menu_Item.php');

class Menu {

    public function admin()
    {
        PHPWS_Core::initModClass('menu', 'Menu_Admin.php');
        Menu_Admin::main();
    }

    public function getPinAllMenus()
    {
        $db = new PHPWS_DB('menus');
        $db->addWhere('pin_all', 1);
        $db->loadClass('menu', 'Menu_Item.php');
        Key::restrictView($db, 'menu');
        return $db->getObjects('Menu_Item');
    }

    /**
     * Grabs all the menus pinned to every page and displays
     * them.
     */
    public function showPinned()
    {
        Layout::addStyle('menu');

        $result = Menu::getPinAllMenus();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return;
        }

        if (empty($result)) {
            return null;
        }

        $GLOBALS['Pinned_Menus'] = $result;

        foreach ($result as $menu) {
            $menu->view();
        }
    }

    public function miniadmin()
    {
        if (!PHPWS_Settings::get('menu', 'miniadmin') ||
            !Current_User::allow('menu')) {
            return;
        }

        if (Menu::isAdminMode()) {
            $vars['command'] = 'disable_admin_mode';
            $vars['return'] = 1;
            MiniAdmin::add('menu', PHPWS_Text::moduleLink(MENU_ADMIN_OFF, 'menu', $vars));
        } else {
            $vars['command'] = 'enable_admin_mode';
            $vars['return'] = 1;
            MiniAdmin::add('menu', PHPWS_Text::moduleLink(MENU_ADMIN_ON, 'menu', $vars));
        }
    }


    /**
     * Function called by mod developer to add their
     * link or to just show the menu on that item
     */
    public function show()
    {
        $seen = array();

        $key = Key::getCurrent();
        if (empty($key) || empty($key->title) || empty($key->url)) {
            return;
        }

        Layout::addStyle('menu');

        $db = new PHPWS_DB('menus');
        $db->addWhere('menu_assoc.key_id', $key->id);
        $db->addWhere('id', 'menu_assoc.menu_id');
        $db->loadClass('menu', 'Menu_Item.php');
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

    public function atLink($url)
    {
        $compare =  PHPWS_Core::getCurrentUrl();
        return $url == $compare;
    }

    public function getSiteLink($menu_id, $parent_id=0, $isKeyed=false, $popup=false)
    {
        $vars['command']   = 'add_site_link';
        $vars['menu_id']   = $menu_id;
        $vars['parent_id'] = $parent_id;

        if (!$isKeyed) {
            if (isset($_GET['curl'])) {
                $vars['dadd'] = urlencode($_GET['curl']);
            } else {
                $vars['dadd'] = urlencode(PHPWS_Core::getCurrentUrl(false));
            }
        }

        $js['link_title'] = dgettext('menu', 'Add other link');
        $js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE, FALSE);
        $js['label'] = MENU_LINK_ADD_SITE;
        if ($popup) {
            $js['label'] .= ' ' . dgettext('menu', 'Add other link');
        }
        $js['width'] = 500;
        $js['height'] = 300;

        return javascript('open_window', $js);
    }

    public function getAddLink($menu_id, $parent_id=null, $popup=false)
    {
        $key = Key::getCurrent();
        if (empty($key->url)) {
            return null;
        }

        if (empty($key)) {
            return null;
        }

        $vars['command'] = 'add_link';
        $vars['menu_id'] = $menu_id;
        if (empty($parent_id)) {
            $parent_id = 0;
        }

        $vars['parent'] = (int)$parent_id;

        $link = MENU_LINK_ADD;
        if ($popup) {
            $link .= ' ' . dgettext('menu', 'Add current page');
            $vars['pu'] = 1;
        }

        if ($key->id) {
            if (!$popup) {
                return sprintf('<a style="cursor : pointer" onclick="add_keyed_link(\'%s\', \'%s\')">%s</a>',
                               $menu_id, $parent_id, $link);
            } else {
                $vars['key_id'] = $key->id;
                return PHPWS_Text::secureLink($link, 'menu', $vars);
            }
        } else {
            // for dummy keys
            if (empty($key->title)) {
                $vars['url']      = urlencode($key->url);
                $js['question']   = dgettext('menu', 'Enter link title');
                $js['address']    = PHPWS_Text::linkAddress('menu', $vars, TRUE, FALSE);
                $js['link'] = $link;
                $js['value_name'] = 'link_title';
                return javascript('prompt', $js);
            } else {
                $vars['link_title'] = urlencode($key->title);
                $vars['url']        = urlencode($key->url);

                if ($popup) {
                    return PHPWS_Text::secureLink($link, 'menu', $vars);
                } else {
                    return sprintf('<a style="cursor : pointer" onclick="add_unkeyed_link(\'%s\', \'%s\', \'%s\', \'%s\')">%s</a>',
                                   $menu_id, $parent_id, $vars['url'], $vars['link_title'], $link);
                }

            }
        }
    }

    public function pinLink($title, $url, $key_id=0)
    {
        $key = substr(md5($title . $url), 0, 8);
        $_SESSION['Menu_Pin_Links'][$key]['title'] = strip_tags($title);
        $_SESSION['Menu_Pin_Links'][$key]['url'] = strip_tags($url);
        if ($key_id) {
            $_SESSION['Menu_Pin_Links'][$key]['key_id'] = $key_id;
        }
    }

    public function getUnpinLink($menu_id, $key_id, $pin_all=0)
    {
        $vars['command'] = 'unpin_menu';
        $vars['menu_id'] = $menu_id;
        if ($key_id >= 0) {
            $vars['key_id'] = $key_id;
        }
        $vars['pin_all'] = $pin_all;
        if ($pin_all) {
            $js['QUESTION']   = dgettext('menu', 'Are you sure you want to unpin this menu from all pages?');
        } else {
            $js['QUESTION']   = dgettext('menu', 'Are you sure you want to unpin this menu from this page?');
        }
        $js['ADDRESS']    = PHPWS_Text::linkAddress('menu', $vars, TRUE);
        $js['LINK']       = MENU_UNPIN;
        return javascript('confirm', $js);
    }

    public function deleteLink($link_id)
    {
        $link = new Menu_Link($link_id);
        if ($link->id) {
            return $link->delete();
        }
    }


    public function enableAdminMode()
    {
        $_SESSION['Menu_Admin_Mode'] = true;
    }


    public function isAdminMode()
    {
        if (isset($_SESSION['Menu_Admin_Mode'])) {
            return $_SESSION['Menu_Admin_Mode'];
        } else {
            return false;
        }
    }

    /**
     * @modified Verdon Vaillancourt
     */
    public function siteMap()
    {
        if (!isset($_GET['site_map'])) {
            PHPWS_Core::errorPage('404');
        }
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if ($_GET['site_map'] == 'all') {
            $db = new PHPWS_DB('menus');
            $result = $db->getObjects('Menu_Item');
            if ($result) {
                foreach($result as $menu) {
                    if (empty($menu->title)) {
                        PHPWS_Core::errorPage('404');
                    }
                    $result = $menu->getLinks();
                    if (PHPWS_Error::logIfError($result)) {
                        PHPWS_Core::errorPage();
                    }
                    $content = array();
                    if (!empty($result)) {
                        Menu::walkLinks($result, $content);
                    }
                    $site['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu', 'Site map');
                    $site['CONTENT'] = implode('', $content);
                    $tpl['site-map'][] = $site;
                }
            } else {
                $tpl['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu', 'Site map');
                $tpl['CONTENT'] = dgettext('menu', 'Sorry, no menus have been created');
            }
        } else {
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
            $tpl['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu', 'Site map');
            $tpl['CONTENT'] = implode('', $content);
        }
        Layout::add(PHPWS_Template::process($tpl, 'menu', 'site_map.tpl'));
    }

    public function walkLinks($links, &$content)
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


    public function quickLink($title, $url)
    {
        if (empty($title) || empty($url)) {
            return false;
        }

        $menus = Menu::getPinAllMenus();
        if (PHPWS_Error::logIfError($menus) || empty($menus)) {
            return false;
        }

        foreach ($menus as $mn) {
            $mn->addRawLink(strip_tags($title), strip_tags($url));
        }
    }

    /**
     * Adds a link to a current pin_all menu
     */
    public function quickKeyLink($key_id)
    {
        if (!$key_id) {
            return false;
        }

        $menus = Menu::getPinAllMenus();
        if (PHPWS_Error::logIfError($menus) || empty($menus)) {
            return false;
        }

        foreach ($menus as $mn) {
            $mn->addLink($key_id);
        }
    }

    public function updateKeyLink($key_id)
    {
        if (empty($key_id)) {
            return false;
        }

        $key = new Key($key_id);

        if ($key->isDummy()) {
            return false;
        }

        PHPWS_Core::initModClass('menu', 'Menu_Link.php');

        $link = new Menu_Link;

        $db = new PHPWS_DB('menu_links');
        $db->addWhere('key_id', (int)$key_id);
        $result = $db->loadObject($link);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        $link->title  = & $key->title;
        $link->url    = & $key->url;
        $link->active = & $key->active;
        return !PHPWS_Error::logIfError($link->save());
    }

}
?>
