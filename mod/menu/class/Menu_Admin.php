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

        // This is the AJAX switch. Byproduct of old module design :(
        switch ($command) {

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

            case 'move_menu':
                $this->moveMenu($request);
                exit();

            case 'move_under':
                $this->moveUnder($request);
                exit();

            case 'add_key_link':
                $this->addKeyLink($request);
                exit();

            case 'remove_key_link':
                $this->removeKeyLink($request);
                exit();

            case 'delete_menu':
                $this->deleteMenu($request);
                exit();

            case 'post_new_menu':
                $this->postMenu($request);
                exit();

            case 'pin_menu':
                $this->pinMenu($request);
                exit();

            case 'unpin_menu':
                $this->unpinMenu($request);
                exit();

            case 'change_display_type':
                $this->changeDisplayType($request);
                exit();

            case 'populate_menu_select':
                exit();
        }

        // This is the display switch or the HTML view switch
        switch ($command) {
            case 'list':
                $title = ('Menus');
                $content = $this->menuList();
                break;

            default:
                throw new \Http\MethodNotAllowedException;
        }

        $tpl['title'] = $title;
        $tpl['content'] = $content;
        if (!empty($message)) {
            $tpl['message'] = $message;
        }
        $template = new \Template($tpl);
        $template->setModuleTemplate('menu', 'admin/main.html');

        Layout::add(PHPWS_ControlPanel::display($template->get()));
    }

    private function changeDisplayType($request)
    {
        \PHPWS_Settings::set('menu', 'display_type',
                (int) $request->getVar('display_type'));
        \PHPWS_Settings::save('menu');
    }

    private function pinMenu($request)
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('menu_assoc');
        $tbl->addValue('menu_id', (int) $request->getVar('menu_id'));
        $tbl->addValue('key_id', (int) $request->getVar('key_id'));
        $tbl->insert();
    }

    private function unpinMenu($request)
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('menu_assoc');
        $tbl->addFieldConditional('menu_id', (int) $request->getVar('menu_id'));
        $tbl->addFieldConditional('key_id', (int) $request->getVar('key_id'));
        $db->delete();
    }

    private function postMenu($request)
    {
        $title = $request->getVar('title');
        $template = $request->getVar('template');
        $menu = new Menu_Item;
        $menu->setTitle($title);
        $menu->setTemplate($template);
        $menu->save();
    }

    private function deleteMenu($request)
    {
        $menu_id = $request->getVar('menu_id');
        $menu = new Menu_Item($menu_id);
        $menu->kill();
    }

    private function addKeyLink(\Request $request)
    {
        $key_id = $request->getVar('key_id');
        $menu_id = $request->getVar('menu_id');

        $menu = new Menu_Item($menu_id);
        $menu->addLink($key_id, 0);
    }

    private function removeKeyLink(\Request $request)
    {
        $key_id = $request->getVar('key_id');
        $menu_id = $request->getVar('menu_id');

        $db = \Database::newDB();
        $ml = $db->addTable('menu_links');
        $ml->addFieldConditional('key_id', $key_id);
        $ml->addFieldConditional('menu_id', $menu_id);
        $links = $db->select();
        if (empty($links)) {
            throw \Exception('Menu link not found');
        }
        foreach ($links as $l) {
            $menu_link = new Menu_Link;
            PHPWS_Core::plugObject($menu_link, $l);
            $menu_link->delete();
        }
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
        if ($request->isVar('next_id')) {
            $next_id = $request->getVar('next_id');
        } else {
            $next_id = 0;
        }
        if ($request->isVar('prev_id')) {
            $prev_id = $request->getVar('prev_id');
        } else {
            $prev_id = 0;
        }

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
                    $ml = $db->addTable('menu_links');
                    $lorder = $ml->getField('link_order');
                    $ml->addValue('link_order',
                            $db->addExpression($lorder . ' + 1'));
                    $ml->addFieldConditional($lorder, $next_link_order, '>=');
                    $ml->addFieldConditional($lorder, $move_link_order, '<');
                    $ml->addFieldConditional('parent', $next_parent);
                    $db->update();
                    //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.link_order >= $next_link_order AND menu_links.link_order < $move_link_order AND menu_links.parent = $next_parent)";
                    //$db->exec($query);
                    $move_link->link_order = $next_link_order;
                } else {
                    // the link was moved AFTER another link
                    $ml = $db->addTable('menu_links');
                    $lorder = $ml->getField('link_order');
                    $ml->addValue('link_order',
                            $db->addExpression($lorder . ' - 1'));
                    $ml->addFieldConditional($lorder, $next_link_order, '<');
                    $ml->addFieldConditional($lorder, $move_link_order, '>');
                    $ml->addFieldConditional('parent', $next_parent);
                    $db->update();
                    //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order < $next_link_order AND menu_links.link_order > $move_link_order AND menu_links.parent = $next_parent)";
                    //$db->exec($query);
                    $move_link->link_order = $next_link_order - 1;
                }
            } else {
                // moved item is on a different level
                $move_link->parent = $next_parent;
                if (!$prev_link) {
                    // moved to top of list
                    $ml = $db->addTable('menu_links');
                    $lorder = $ml->getField('link_order');
                    $ml->addValue('link_order',
                            $db->addExpression($lorder . ' + 1'));
                    $ml->addFieldConditional('parent', $next_parent);
                    $db->update();
                    //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.parent = $next_parent)";
                    //$db->exec($query);
                    $move_link->link_order = 1;
                } else {
                    // there is a previous link so we number from there
                    $ml = $db->addTable('menu_links');
                    $lorder = $ml->getField('link_order');
                    $ml->addValue('link_order',
                            $db->addExpression($lorder . ' + 1'));
                    $ml->addFieldConditional($lorder, $prev_link_order, '>');
                    $ml->addFieldConditional('parent', $next_parent);
                    $db->update();
                    //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order+1 WHERE (menu_links.link_order > $prev_link_order AND menu_links.parent = $next_parent)";
                    //$db->exec($query);
                    $move_link->link_order = $prev_link_order + 1;
                }
                // reset links where moved item was
                $db->reset();
                $ml = $db->addTable('menu_links');
                $lorder = $ml->getField('link_order');
                $ml->addValue('link_order', $db->addExpression($lorder . ' - 1'));
                $ml->addFieldConditional($lorder, $move_link_order, '>');
                $ml->addFieldConditional('parent', $move_parent);
                $db->update();
                //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $move_parent)";
                //$db->exec($query);
            }
        } else {
            // moved item is at the end of a list
            if ($move_parent == $prev_parent) {
                // moved item is on the same level at the bottom of the list, move everything that was after moved down a peg
                $ml = $db->addTable('menu_links');
                $lorder = $ml->getField('link_order');
                $ml->addValue('link_order', $db->addExpression($lorder . ' - 1'));
                $ml->addFieldConditional($lorder, $move_link_order, '>');
                $ml->addFieldConditional('parent', $move_parent);
                $db->update();
                //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $prev_parent)";
                //$db->exec($query);
                $move_link->link_order = $prev_link_order;
            } else {
                // moved item is on a different level, reset links where moved link was
                $ml = $db->addTable('menu_links');
                $lorder = $ml->getField('link_order');
                $ml->addValue('link_order', $db->addExpression($lorder . ' - 1'));
                $ml->addFieldConditional($lorder, $move_link_order, '>');
                $ml->addFieldConditional('parent', $move_parent);
                $db->update();
                //$query = "UPDATE menu_links SET menu_links.link_order=menu_links.link_order-1 WHERE (menu_links.link_order > $move_link_order AND menu_links.parent = $move_parent)";
                //$db->exec($query);
                $move_link->link_order = $prev_link_order + 1;
                $move_link->parent = $prev_parent;
            }
        }
        $move_link->save();
    }

    private function moveMenu(\Request $request)
    {
        $move_id = $request->getVar('move_id');
        $move_menu = new Menu_Item($move_id);

        if ($request->isVar('next_id')) {
            $next_id = $request->getVar('next_id');
            $next_menu = new Menu_Item($next_id);
        } else {
            $next_id = 0;
        }

        if ($request->isVar('prev_id')) {
            $prev_id = $request->getVar('prev_id');
            $prev_menu = new Menu_Item($prev_id);
        } else {
            $prev_id = 0;
        }

        $db = \Database::newDB();
        $tbl = $db->addTable('menus');
        $queue = $tbl->getField('queue');

        if ($next_id == 0) {
            // moved to end of list
            $exp = $db->getExpression('max(' . $queue . ')', 'max_queue');
            $tbl->addField($exp);
            $last_queue = $db->selectColumn();
            $tbl->addValue('queue', $db->getExpression($queue . ' - 1'));
            $tbl->addFieldConditional('queue', $move_menu->queue, '>');
            $db->update();
            $move_menu->queue = $last_queue;
        } elseif ($prev_id == 0) {
            // moved to beginning of list
            $tbl->addValue('queue', $db->getExpression($queue . ' + 1'));
            $tbl->addFieldConditional('queue', $move_menu->queue, '<');
            $db->update();
            $move_menu->queue = 1;
        } else {
            // moved in the middle of list
            if ($move_menu->queue < $next_menu->queue) {
                $tbl->addValue('queue', $db->getExpression($queue . ' - 1'));
                $tbl->addFieldConditional('queue', $move_menu->queue, '>=');
                $tbl->addFieldConditional('queue', $next_menu->queue, '<');
                $move_menu->queue = $prev_menu->queue;
            } else {
                $tbl->addValue('queue', $db->getExpression($queue . ' + 1'));
                $tbl->addFieldConditional('queue', $next_menu->queue, '>=');
                $tbl->addFieldConditional('queue', $move_menu->queue, '<');
                $move_menu->queue = $next_menu->queue;
            }
            $db->update();
        }
        $move_menu->save();
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
        $menu->_show_all = true;
        $data['html'] = $menu->view(true);
        $data['pin_all'] = $menu->pin_all;
        echo json_encode($data);
    }

    private function menuList()
    {
        \Layout::addStyle('menu', 'admin.css');
        javascript('jquery');
        javascript('jquery_ui');
        $template = new \Template;
        $template->setModuleTemplate('menu', 'admin/administrate.html');

        $db = new PHPWS_DB('menus');
        $db->addOrder('queue');
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
            $tpl['first_menu'] = $first_menu->view(true);
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
        $vars['delete_menu_message'] = t('Are you sure you want to delete this menu and links?');
        $vars['edit'] = t('Edit');
        $vars['title_error'] = t('Please enter a menu title');
        $vars['pin_all'] = t('Shown on all pages');
        $vars['pin_some'] = t('Shown on some pages');

        $jvar = json_encode($vars);
        $script = <<<EOF
<script type="text/javascript">var translate = $jvar;</script>
EOF;
        \Layout::addJSHeader($script);
        \Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/menu/javascript/administrate/script.js"></script>');

        $included_result = PHPWS_File::listDirectories(PHPWS_Template::getTemplateDirectory('menu') . 'menu_layout/');

        $tpl['templates'] = null;
        foreach ($included_result as $menu_tpl) {
            $tpl['templates'] .= "<option>$menu_tpl</option>";
        }

        $tpl['display_type'] = \PHPWS_Settings::get('menu', 'display_type');
        if ($first_menu->pin_all) {
            $tpl['pin_all'] = $vars['pin_all'];
            $tpl['pin_button_class'] = 'btn-primary';
        } else {
            $tpl['pin_all'] = $vars['pin_some'];
            $tpl['pin_button_class'] = 'btn-default';
        }

        $template->addVariables($tpl);
        return $template->get();
    }

}

?>