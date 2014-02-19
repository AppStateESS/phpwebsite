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

            case 'menu_options':
                $this->menuOptions($request);
                exit();

            case 'move_under':
                $this->moveUnder($request);
                exit();

            case 'transfer_link':
                $this->transferLink($request);
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

            case 'post_menu':
                $this->postMenu($request);
                \PHPWS_Core::goBack();
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

            case 'menu_data':
                $this->menuData($request);
                exit();

            case 'pin_all':
                $this->menuPinAll($request);
                exit();

            case 'clear_image':
                $this->clearImage($request);
                exit();
        }

        // This is the display switch or the HTML view switch
        switch ($command) {
            case 'list':
                $title = ('Menus');
                $content = $this->menuList();
                break;

            case 'reset_menu':
                if (!\Current_User::isDeity() && !\Current_User::authorized('menu')) {
                    throw new \Http\MethodNotAllowedException;
                }
                $this->resetMenu();
                PHPWS_Core::goBack();
                exit();

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

    private function clearImage(\Request $request)
    {
        $menu = new Menu_Item($request->getVar('menu_id'));
        $menu->deleteImage();
        $menu->setAssocImage(null);
        $menu->save();
    }

    /**
     * Resets the queue order of the menus
     */
    private function resetMenu()
    {
        $db = \Database::newDB();
        $m = $db->addTable('menus');
        $m->addField('id');
        $m->addOrderBy('queue');
        $result = $db->select();

        if (empty($result)) {
            return;
        }

        $queue = 1;
        foreach ($result as $row) {
            $db->clearConditional();
            $m->resetValues();
            $m->addValue('queue', $queue);
            $m->addFieldConditional('id', $row['id']);
            $db->update();
            $queue++;
        }
    }

    private function transferLink($request)
    {
        $menu_id = $request->getVar('menu_id');
        $link_id = $request->getVar('link_id');

        $link = new Menu_Link($link_id);
        $old_menu_id = $link->menu_id;
        $link->parent = 0;
        $link->menu_id = $menu_id;
        $link->link_order = 0;
        $link->save();

        $children = $this->getAllChildren($link_id);
        if (!empty($children)) {
            $db = \Database::newDB();
            $t1 = $db->addTable('menu_links');
            $t1->addFieldConditional('id', $children, 'in');
            $t1->addValue('menu_id', $menu_id);
            $db->update();
        }

        $menu = new Menu_Item($menu_id);
        $menu->reorderLinks();
        $menu = new Menu_Item($old_menu_id);
        $menu->reorderLinks();
    }


    private function getAllChildren($id, $kids = null)
    {
        if (empty($kids)) {
            $kids = array();
        }

        $db = \Database::newDB();
        $t1 = $db->addTable('menu_links');
        $t1->addField('id');
        $t1->addFieldConditional('parent', $id);
        while ($col = $db->selectColumn()) {
            $kids[] = $col;
            $kids = $this->getAllChildren($col, $kids);
        }
        return $kids;
    }

    private function menuPinAll($request)
    {
        $menu = new Menu_Item($request->getVar('menu_id'));
        $menu->pin_all = $request->getVar('pin_all') ? 1 : 0;
        $menu->save();
    }

    private function menuData($request)
    {
        $menu = new Menu_Item($request->getVar('menu_id'));
        echo json_encode(array('title' => $menu->title, 'template' => $menu->template,
            'assoc_key' => $menu->getAssocKey(), 'assoc_url' => $menu->getAssocUrl(),
            'assoc_image_thumbnail' => $menu->getAssocImageThumbnail()));
    }

    private function changeDisplayType($request)
    {
        \PHPWS_Settings::set('menu', 'display_type',
                (int) $request->getVar('display_type'));
        \PHPWS_Settings::save('menu');
    }

    private function menuOptions($request)
    {
        $menu_id = (int) $request->getVar('menu_id');

        $db = \Database::newDB();
        $t1 = $db->addTable('menus');
        $t1->addFieldConditional('id', $menu_id, '!=');
        $t1->addOrderBy('title');
        $result = $db->select();
        if (empty($result)) {
            return;
        }

        $options[] = '<option value="" selected disabled>' . t('Move link to menu below...') . '</option>';
        foreach ($result as $menu) {
            extract($menu);
            $options[] = "<option value='$id'>$title</option>";
        }
        echo implode("\n", $options);
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

        $menu = new Menu_Item($request->getVar('menu_id'));

        if ($request->isVar('assoc_key')) {
            $assoc_key = $request->getVar('assoc_key');
        } elseif ($menu->assoc_key) {
            $assoc_key = $menu->assoc_key;
        } else {
            $assoc_key = 0;
        }
        $assoc_url = trim(strip_tags($request->getVar('assoc_url')));


        $menu->setTitle($title);
        $menu->setTemplate($template);
        $menu->assoc_url = null;
        $menu->assoc_key = 0;
        if ($assoc_key) {
            $menu->setAssocKey($assoc_key);
        } elseif (!empty($assoc_url)) {
            $menu->setAssocUrl($assoc_url);
        }

        if ($request->isUploadedFile('assoc_image')) {
            $menu->deleteImage();

            $file = $request->getUploadedFileArray('assoc_image');
            $file_name = randomString(12) . '.' . str_replace('image/', '',
                            $file['type']);

            \PHPWS_File::fileCopy($file['tmp_name'], 'images/menu/', $file_name,
                    false, true);
            \PHPWS_File::makeThumbnail($file_name, 'images/menu/',
                    'images/menu/', 200);
            $menu->setAssocImage('images/menu/' . $file_name);
        }

        $menu->save();
    }

    private function deleteMenu($request)
    {
        $menu_id = $request->getVar('menu_id');
        $menu = new Menu_Item($menu_id);
        $menu->kill();
        $this->resetMenu();
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
                    $move_link->link_order = $prev_link_order + 1;
                }
                // reset links where moved item was
                $db = \Database::newDB();
                $ml = $db->addTable('menu_links');
                $lorder = $ml->getField('link_order');
                $ml->addValue('link_order', $db->addExpression($lorder . ' - 1'));
                $ml->addFieldConditional($lorder, $move_link_order, '>');
                $ml->addFieldConditional('parent', $move_parent);
                $db->update();
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
                $move_link->link_order = $prev_link_order;
            } else {
                // moved item is on a different level, reset links where moved link was
                $ml = $db->addTable('menu_links');
                $lorder = $ml->getField('link_order');
                $ml->addValue('link_order', $db->addExpression($lorder . ' - 1'));
                $ml->addFieldConditional($lorder, $move_link_order, '>');
                $ml->addFieldConditional('parent', $move_parent);
                $db->update();
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
            $link->setUrl($url);
        } else {
            $link = new Menu_Link;
            $link->setTitle($title);
            $link->setMenuId($menu_id);
            if ($key_id !== '0') {
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

    private function getUsedKeys()
    {
        $db = \Database::newDB();
        $db->addTable('menus')->addField('assoc_key', 'key_id');
        $db2 = \Database::newDB();
        $db2->addTable('menu_links')->addField('key_id');
        $union = new \Database\Union(array($db, $db2));
        $rows = $union->select();

        foreach ($rows as $r) {
            $keys[] = $r['key_id'];
        }
        return $keys;
    }

    private function keySelect()
    {
        $keys = array();
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

        $keys = $this->getUsedKeys();

        $opt[] = '<option value="0"></option>';
        foreach ($key_list as $k) {
            $id = $title = null;
            extract($k);
            if (in_array($id, $keys)) {
                $opt[] = "<option value='$id' disabled='disabled'>*$title</option>";
            } else {
                $opt[] = "<option value='$id'>$title</option>";
            }
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
        $menu->template = 'admin';
        $data['html'] = $menu->view(true);
        $data['pin_all'] = $menu->pin_all;
        echo json_encode($data);
    }

    private function menuList()
    {
        \Layout::addStyle('menu', 'admin.css');
        javascript('jquery');
        javascript('jquery_ui');
        //commenting out for now. problem clearing select dropdown
        //javascript('select2');

        $template = new \Template;
        $template->setModuleTemplate('menu', 'admin/administrate.html');
        $first_menu_pin_all = 0;
        $first_menu_template = null;

        $db = new PHPWS_DB('menus');
        $db->addOrder('queue');
        $result = $db->getObjects('Menu_Item');
        $first_menu = null;
        if (!empty($result)) {
            foreach ($result as $menu) {
                $menu->_show_all = true;
                if (empty($first_menu)) {
                    $first_menu = $menu;
                    $active = 'active';
                } else {
                    $active = null;
                }
                $tpl['menus'][] = array('title' => $menu->title, 'id' => $menu->id, 'active' => $active);
            }
            $first_menu_template = $first_menu->template;
            // for display, use the admin template
            $first_menu->template = 'admin';
            $tpl['first_menu'] = $first_menu->view(true);
            $first_menu_pin_all = $first_menu->pin_all;
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

        if (PHPWS_Settings::get('menu', 'display_type')) {
            $vars['pin_all'] = null;
            $vars['pin_some'] = null;
        } else {
            $vars['pin_all'] = t('Shown on all pages');
            $vars['pin_some'] = t('Shown on some pages');
        }

        $jvar = json_encode($vars);
        $script = <<<EOF
<script type="text/javascript">var translate = $jvar; var fmp=$first_menu_pin_all;</script>
EOF;
        \Layout::addJSHeader($script);
        \Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/menu/javascript/administrate/script.js"></script>');

        $main_menu_templates = PHPWS_File::listDirectories(PHPWS_Template::getTemplateDirectory('menu') . 'menu_layout/');
        $theme_menu_templates = PHPWS_File::listDirectories(PHPWS_Template::getTplDir('menu') . 'menu_layout/');

        $menu_tpls[] = '<optgroup label="' . t('Menu module templates') . '">';
        foreach ($main_menu_templates as $menu_tpl) {
            if ($first_menu_template == $menu_tpl) {
                $selected = ' selected="selected"';
            } else {
                $selected = null;
            }
            $menu_tpls[] = "<option value='$menu_tpl'$selected>$menu_tpl</option>";
        }
        $menu_tpls[] = '</optgroup>';

        if (!empty($theme_menu_templates)) {
            $menu_tpls[] = '<optgroup label="' . t('Theme templates') . '">';
            foreach ($theme_menu_templates as $menu_tpl) {
                if ($first_menu_template == $menu_tpl) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = null;
                }
                $menu_tpls[] = "<option value='$menu_tpl'$selected>$menu_tpl</option>";
            }
            $menu_tpls[] = '</optgroup>';
        }

        $tpl['templates'] = implode('', $menu_tpls);

        $tpl['display_type'] = \PHPWS_Settings::get('menu', 'display_type');
        if (isset($first_menu) && $first_menu->pin_all) {
            $tpl['pin_all'] = $vars['pin_all'];
            $tpl['pin_button_class'] = 'btn-primary';
        } else {
            $tpl['pin_all'] = $vars['pin_some'];
            $tpl['pin_button_class'] = 'btn-default';
        }

        if (\Current_User::isDeity()) {
            $tpl['reset_menu_link'] = PHPWS_Text::linkAddress('menu',
                            array('command' => 'reset_menu'), true);
        } else {
            $tpl['reset_menu_link'] = '#';
        }

        $template->addVariables($tpl);
        return $template->get();
    }

}

?>