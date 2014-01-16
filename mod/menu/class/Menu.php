<?php

/**
 * Main functionality class for Menu module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::initModClass('menu', 'Menu_Item.php');

class Menu {

    public static function admin()
    {
        PHPWS_Core::initModClass('menu', 'Menu_Admin.php');
        $admin = new Menu_Admin;
        $admin->main();
    }

    public static function getPinAllMenus()
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
    public static function showPinned()
    {
        $result = Menu::getPinAllMenus();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return;
        }

        if (empty($result)) {
            return null;
        }

        $GLOBALS['Pinned_Menus'] = $result;

        foreach ($result as $menu) {
            Layout::set($menu->view(), 'menu', 'menu_' . $menu->id);
        }
    }

    public static function miniadmin()
    {
        if (!Current_User::allow('menu')) {
            return;
        }

        MiniAdmin::add('menu',
                \PHPWS_Text::secureLink('Administrate menus', 'menu',
                        array('command' => 'list')));

        $key = \Key::getCurrent();

        $link_list = self::getLinkList();

        if ($key && !$key->isDummy()) {
            javascript('jquery');
            \Layout::addJSHeader('<script type="text/javascript" src="' .
                    PHPWS_SOURCE_HTTP . 'mod/menu/javascript/administrate/minilink.js"></script>');
            $found = false;
            $used_menus = array();
            foreach ($link_list as $link) {
                $menu_id = 0;
                extract($link);
                if ($key_id == $key->id) {
                    if (!in_array($menu_id, $used_menus)) {
                        $used_menus[] = $menu_id;
                        MiniAdmin::add('menu',
                                '<a href="javascript:void(0)" data-key-id="' . $key->id
                                . '" data-menu-id="' . $menu_id
                                . '" id="menu-remove-page">' . t('Remove from %s',
                                        $menu_title) . '</a>');
                        $found = true;
                    }
                }
            }
            if (!$found) {
                self::miniadminAddMenu($key);
            }

            self::miniadminPinMenu($key);
            self::miniadminUnpin($key);
        }
    }

    private static function miniadminAddMenu($key)
    {
        $menus = self::getMenuListing();

        $choice[] = '<div class="input-group-sm" style="margin-bottom : 5px"><select class="form-control" name="menu_id" id="menu-add-page" data-key-id="'
                . $key->id . '">';
        $choice[] = '<option value="0" disabled="disabled" selected="selected"><i class="fa fa-caret-down"></i>' . t('Add link to menu') . '</option>';
        foreach ($menus as $menu) {
            $choice[] = '<option value="' . $menu['id'] . '">' . $menu['title'] . '</option>';
        }
        $choice[] = '</select></div>';

        $menu_choice = implode("\n", $choice);
        MiniAdmin::add('menu', $menu_choice);
    }

    private static function miniadminUnpin($key)
    {
        $menus = self::getMenuListing(false);
        $assoc = self::getAssociations($key->id);

        if (!empty($assoc)) {
            foreach ($assoc as $a) {
                $ignore[] = $a['menu_id'];
            }
        } else {
            $ignore = array();
        }

        $choice[] = '<div class="input-group-sm" style="margin-bottom : 5px"><select class="form-control" name="menu_id" id="menu-unpin-page" data-key-id="'
                . $key->id . '">';
        $choice[] = '<option value="0" disabled="disabled" selected="selected"><i class="fa fa-caret-down"></i>' . t('Remove menu') . '</option>';
        $menu_found = false;
        foreach ($menus as $menu) {
            if (in_array($menu['id'], $ignore)) {
                $menu_found = true;
                $choice[] = '<option value="' . $menu['id'] . '">' . $menu['title'] . '</option>';
            }
        }
        if (!$menu_found) {
            // No menus need removing
            return;
        }
        $choice[] = '</select></div>';

        $menu_choice = implode("\n", $choice);
        MiniAdmin::add('menu', $menu_choice);
    }

    private static function miniadminPinMenu($key)
    {
        $menus = self::getMenuListing(false);
        $assoc = self::getAssociations($key->id);

        if (!empty($assoc)) {
            foreach ($assoc as $a) {
                $ignore[] = $a['menu_id'];
            }
        } else {
            $ignore = array();
        }

        $choice[] = '<div class="input-group-sm" style="margin-bottom : 5px"><select class="form-control" name="menu_id" id="menu-pin-page" data-key-id="'
                . $key->id . '">';
        $choice[] = '<option value="0" disabled="disabled" selected="selected"><i class="fa fa-caret-down"></i>' .
                t('Show menu here') . '</option>';
        $menu_found = false;
        foreach ($menus as $menu) {
            if (!in_array($menu['id'], $ignore)) {
                $menu_found = true;
                $choice[] = '<option value="' . $menu['id'] . '">' . $menu['title'] . '</option>';
            }
        }
        if (!$menu_found) {
            // This means all menus are on this key
            return;
        }
        $choice[] = '</select></div>';
        $content = implode("\n", $choice);
        Miniadmin::add('menu', $content);
    }

    private static function getAssociations($key_id)
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('menu_assoc');
        $tbl->addField('menu_id');
        $tbl->addFieldConditional('key_id', $key_id);
        return $db->select();
    }

    public static function getLinkList()
    {
        $db = \Database::newDB();
        $t1 = $db->addTable('menu_links');
        $t2 = $db->addTable('menus');
        $db->joinResources($t1, $t2,
                $db->createConditional($t1->getField('menu_id'),
                        $t2->getField('id'), '='));
        $t1->addField('id');
        $t1->addField('key_id');
        $t1->addField('menu_id');
        $t2->addField('title', 'menu_title');
        $link_list = $db->select();
        return $link_list;
    }

    /**
     * Function called by mod developer to add their
     * link or to just show the menu on that item
     */
    public static function show()
    {
        $seen = array();

        $key = Key::getCurrent();
        if (empty($key) || empty($key->title) || empty($key->url)) {
            return;
        }

        $db = new PHPWS_DB('menus');
        $db->addWhere('menu_assoc.key_id', $key->id);
        $db->addWhere('id', 'menu_assoc.menu_id');
        $db->loadClass('menu', 'Menu_Item.php');
        Key::restrictView($db, 'menu');
        $result = $db->getObjects('Menu_Item');

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        } elseif (!empty($result)) {
            foreach ($result as $menu) {
                $seen[] = $menu->id;
                Layout::set($menu->view(), 'menu', 'menu_' . $menu->id);
            }
        }
    }

    public function atLink($url)
    {
        $compare = PHPWS_Core::getCurrentUrl();
        return $url == $compare;
    }

    public static function deleteLink($link_id)
    {
        $link = new Menu_Link($link_id);
        if ($link->id) {
            return $link->delete();
        }
    }

    /**
     * @modified Verdon Vaillancourt
     */
    public static function siteMap()
    {
        if (!isset($_GET['site_map'])) {
            PHPWS_Core::errorPage('404');
        }
        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if ($_GET['site_map'] == 'all') {
            $db = new PHPWS_DB('menus');
            $result = $db->getObjects('Menu_Item');
            if ($result) {
                foreach ($result as $menu) {
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
                    $site['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu',
                                    'Site map');
                    $site['CONTENT'] = implode('', $content);
                    $tpl['site-map'][] = $site;
                }
            } else {
                $tpl['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu',
                                'Site map');
                $tpl['CONTENT'] = dgettext('menu',
                        'Sorry, no menus have been created');
            }
        } else {
            $menu = new Menu_Item((int) $_GET['site_map']);
            if (empty($menu->title)) {
                PHPWS_Core::errorPage('404');
            }

            $result = $menu->getLinks();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                PHPWS_Core::errorPage();
            }
            $content = array();
            if (!empty($result)) {
                Menu::walkLinks($result, $content);
            }
            $tpl['TITLE'] = $menu->getTitle() . ' - ' . dgettext('menu',
                            'Site map');
            $tpl['CONTENT'] = implode('', $content);
        }
        Layout::add(PHPWS_Template::process($tpl, 'menu', 'site_map.tpl'));
    }

    public static function walkLinks($links, &$content)
    {
        $admin = \Current_User::allow('menu');
        $content[] = '<ol>';
        foreach ($links as $link) {
            $content[] = '<li>';
            $content[] = $link->getAnchorTag($admin);
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
    public static function quickKeyLink($key_id)
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

    public static function updateKeyLink($key_id)
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
        $db->addWhere('key_id', (int) $key_id);
        $result = $db->loadObject($link);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        $link->title = & $key->title;
        $link->url = & $key->url;
        $link->active = & $key->active;
        return !PHPWS_Error::logIfError($link->save());
    }

    public static function getMenuListing($include_pin_all = true)
    {
        $db2 = \Database::newDB();
        $t2 = $db2->addTable('menus');
        $t2->addOrderBy($t2->addField('title'));
        $t2->addField('id');
        if (!$include_pin_all) {
            $t2->addFieldConditional('pin_all', 0);
        }
        $menus = $db2->select();
        return $menus;
    }

    public static function categoryView()
    {
        $active_menu = self::getCurrentActiveMenu();
        if ($active_menu == 0) {
            $menu_tpl['home_active'] = 1;
        } else {
            $menu_tpl['home_active'] = 0;
        }

        $db = \Database::newDB();
        $m = $db->addTable('menus');
        $k = $db->addTable('phpws_key');
        $k->addField('url');
        $db->joinResources($m, $k,
                $db->createConditional($m->getField('assoc_key'),
                        $k->getField('id'), '='), 'left');
        $m->addOrderBy($m->getField('queue'));

        $key = \Key::getCurrent();
        if ($key && $key->id) {
            $current_key_id = $key->id;
        } else {
            $current_key_id = -1;
        }
        $menus = $db->select();
        if (empty($menus)) {
            return;
        }
        foreach ($menus as $m) {
            $menu = new Menu_Item;
            PHPWS_Core::plugObject($menu, $m);
            $menu->_show_all = true;
            if (empty($menu->assoc_url)) {
                $menu->setAssocUrl($m['url']);
            }
            // if the current menu matches a used link (either by it being in the
            // in the menu or associated to it) mark as ACTIVE
            $active = ($active_menu == $menu->id || $current_key_id == $menu->assoc_key) ? 1 : 0;
            // if there is not an assoc key, them menu is using drop downs, so
            // we do not add the side menu
            if ($active && $menu->assoc_key) {
                Layout::set($menu->view(), 'menu', 'menu_' . $menu->id);
            }

            $menu_tpl['menus'][] = self::getCategoryViewLine($menu, $active);
        }
        $template = new \Template($menu_tpl);
        $template->setModuleTemplate('menu', 'category_view/category_menu.html');
        \Layout::add($template->get(), 'menu', 'top_view');
    }

    private static function getCategoryViewLine($menu, $active)
    {
        $template = new \Template();
        $line = array('active' => $active, 'title' => $menu->title, 'assoc_key' => $menu->assoc_key);
        if ($menu->assoc_key || !empty($menu->assoc_url)) {
            $line['assoc_url'] = $menu->getAssocUrl();
            $template->setModuleTemplate('menu',
                    'category_view/associated_menu.html');
        } else {
            $line['links'] = $menu->displayLinks();
            $template->setModuleTemplate('menu',
                    'category_view/dropdown_menu.html');
        }
        $template->addVariables($line);
        return $template->get();
    }

    /**
     * Determines the current menu to be shown based on the current key.
     * This is for category view only.
     * @return int
     */
    private static function getCurrentActiveMenu()
    {
        $key = \Key::getCurrent(true);
        if (empty($key) || $key->isDummy(true)) {
            return -1;
        } elseif ($key->isHomeKey()) {
            return 0;
        }
        $db = \Database::newDB();
        $t = $db->addTable('menu_links');
        $t->addFieldConditional('key_id', $key->id);
        $t->addField('menu_id');
        $db->setLimit(1);
        $row = $db->selectOneRow();
        return $row['menu_id'];
    }

}

?>
