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
        $request = \Server::getCurrentRequest();
        $title = $content = $message = NULL;

        PHPWS_Core::initModClass('menu', 'Menu_Item.php');

        if (!Current_User::allow('menu')) {
            Current_User::disallow(dgettext('menu',
                            'User attempted access to Menu administration.'));
            return;
        }

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = 'list';
        }

        switch ($command) {

            case 'list':
                $title = ('Menus');
                $content = $this->menuList();
                break;

            case 'adminlinks':
                $this->adminLinks($request);
                exit();

            case 'delete_link':
                $this->deleteLink($request);
                exit();

            case 'key_select':
                $this->keySelect();
                exit();

            case 'post_link':
                $this->postLink($request);
                exit();

            case 'move_link':
                $this->moveLink($request);
                exit();

            case 'move_under':
                $this->moveUnder($request);
                exit();
        } // end command switch

        $tpl['title'] = $title;
        $tpl['content'] = $content;
        if (!empty($message)) {
            $tpl['message'] = $message;
        }
        $template = new \Template($tpl);
        $template->setModuleTemplate('menu', 'admin/main.html');

        Layout::add(PHPWS_ControlPanel::display($template->get()));
    }

    private function moveUnder(\Request $request)
    {
        $move_from = new Menu_Link($request->getVar('move_from'));
        $move_to = new Menu_Link($request->getVar('move_to'));

        $menu = new Menu_Item($move_from->menu_id);

        $move_from->parent = $move_to->id;
        $move_from->link_order = null;
        $move_from->save();

        $menu->reorderLinks();
    }

    private function moveLink(\Request $request)
    {
        $move_id = $request->getVar('move_id');
        $next_id = $request->getVar('next_id');
        $prev_id = $request->getVar('prev_id');

        $move_link = new Menu_Link($move_id);
        $move_link_order = $move_link->link_order;
        $move_parent = $move_link->parent;

        if ($next_id) {
            $next_link = new Menu_Link($next_id);
            $next_link_order = $next_link->link_order;
            $next_parent = $next_link->parent;
        } else {
            $next_link = null;
            $next_link_order = null;
            $next_parent = null;
        }

        if ($prev_id) {
            $prev_link = new Menu_Link($prev_id);
            $prev_link_order = $prev_link->link_order;
            $prev_parent = $prev_link->parent;
        } else {
            $prev_link = null;
            $prev_link_order = null;
            $prev_parent = null;
        }

        $db = \Database::newDB();
        if ($next_link) {
            // moved item is not at the end of a list
            if ($move_parent == $next_parent) {
                // moved item is on the same level
                if ($move_link_order > $next_link_order) {
                    // the link was moved BEFORE another link
                    $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.link_order >= $next_link_order AND menu_links.link_order < $move_link_order AND menu_links.parent = $next_parent)";
                    $db->exec($query);
                    $move_link->link_order = $next_link_order;
                } else {
                    // the link was moved AFTER another link
                    $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order < $next_link_order AND menu_links.link_order > $move_link_order AND menu_links.parent = $next_parent)";
                    $db->exec($query);
                    $move_link->link_order = $next_link_order - 1;
                }
            } else {
                // moved item is on a different level
                $move_link->parent = $next_parent;
                if (!$prev_link) {
                    // moved to top of list
                    $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.parent = $next_parent)";
                    $db->exec($query);
                    $move_link->link_order = 1;
                } else {
                    // there is a previous link so we number from there
                    $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.link_order > $prev_link_order AND menu_links.parent = $next_parent)";
                    $db->exec($query);
                    $move_link->link_order = $prev_link_order + 1;
                }
                // reset links where moved item was
                $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $move_parent)";
                $db->exec($query);
            }
        } else {
            // moved item is at the end of a list
            if ($move_parent == $prev_parent) {
                // moved item is on the same level at the bottom of the list, move everything that was after moved down a peg
                $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $prev_parent)";
                $db->exec($query);
                $move_link->link_order = $prev_link_order;
            } else {
                // moved item is on a different level, reset links where moved link was
                $query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $move_parent)";
                $db->exec($query);
                $move_link->link_order = $prev_link_order+1;
                $move_link->parent = $prev_parent;
            }
        }
        $move_link->save();
    }

    private function postLink(\Request $request)
    {
        $link_id = $menu_id = $title = $url = $key_id = null;
        $s = $request->getRequestVars();
        extract($s);
        if ($link_id) {
            $link = new Menu_Link($link_id);
            $link->setTitle($title);
        } else {
            $link = new Menu_Link;
            $link->setTitle($title);
            $link->setMenuId($menu_id);
            if ($key_id !== '--') {
                $key = new Key($key_id);
                $link->setKeyId($key_id);
                $url = $key->url;
            } else {
                $link->key_id = 0;
            }
            $link->setUrl($url);
        }
        $result = $link->save();
    }

    private function keySelect()
    {
        $db = \Database::newDB();
        $key = $db->addTable('phpws_key');
        $key->addOrderBy($key->getField('title'));
        $key->addField('id');
        $key->addField('title');
        $key->addFieldConditional('active', 1);
        $key->addFieldConditional('module', 'pagesmith');
        $key_list = $db->select();
        if (empty($key_list)) {
            return;
        }
        $opt[] = '<option value="--"></option>';
        foreach ($key_list as $k) {
            extract($k);
            $opt[] = "<option value='$id'>$title</option>";
        }
        echo implode("\n", $opt);
    }

    private function deleteLink($request)
    {
        $link_id = $request->getVar('link_id');
        $link = new Menu_Link($link_id);
        $link->delete();
    }

    private function adminLinks($request)
    {
        $menu = new \Menu_Item($request->getVar('menu_id'));
        echo $menu->view(false, true);
    }

    private function menuList()
    {
        \Layout::addStyle('menu', 'admin.css');
        javascript('jquery');
        javascript('jquery_ui');
        $template = new \Template;
        $template->setModuleTemplate('menu', 'admin/administrate.html');

        $db = new PHPWS_DB('menus');
        $db->addOrder('title');
        $result = $db->getObjects('Menu_Item');
        if (!empty($result)) {
            $first_menu = null;
            foreach ($result as $menu) {
                $menu->template = 'basic';
                $menu->_show_all = true;
                if (empty($first_menu)) {
                    $first_menu = $menu;
                }
                $tpl['menus'][] = array('title' => $menu->title, 'id' => $menu->id);
            }
            $tpl['first_menu'] = $first_menu->view(false, true);
            $first_menu_id = $first_menu->id;
        } else {
            $first_menu_id = 0;
            $tpl['first_menu'] = null;
        }

        $vars['delete'] = t('Delete');
        $vars['confirm_delete'] = t('Confirm deletion');
        $vars['first_menu_id'] = $first_menu_id;
        $vars['authkey'] = \Current_User::getAuthKey();
        $vars['blank_title'] = t('Title must not be blank');
        $vars['title_error'] = t('Make sure you have filled in the required inputs.');
        $vars['url_error'] = t('Please enter a url or choose a PageSmith page.');

        $jvar = json_encode($vars);
        $script = <<<EOF
<script type="text/javascript">var z = $jvar;</script>
EOF;
        \Layout::addJSHeader($script);
        \Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/menu/javascript/administrate/script.js"></script>');

        $template->addVariables($tpl);
        return $template->get();
    }

}

?>