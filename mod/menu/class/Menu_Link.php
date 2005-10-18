<?php

define('MENU_MISSING_INFO', 1);

class Menu_Link {
    var $id         = 0;
    var $menu_id    = 0;
    var $key_id     = 0;
    var $title      = NULL;
    var $parent     = 0;
    var $active     = 1;
    var $link_order = 1;
    var $_error     = NULL;
    var $_children  = NULL;
    var $_db        = NULL;
    var $_key       = NULL;

    function Menu_Link($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }
    }

    function init()
    {
        $db = $this->getDB();
        $db = & new PHPWS_DB('menu_links');
        $db->loadObject($this);
        $this->loadChildren();
        $this->loadKey();
    }

    function &getDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('menu_links');
        }
        $this->_db->reset();
        return $this->_db;
    }

    function loadKey()
    {
        $this->_key = & new Key($this->key_id);
    }

    /**
     * Grabs all the child links under the current link
     */
    function loadChildren()
    {
        $db = $this->getDB();
        $db->addWhere('parent', $this->id);
        $db->addOrder('link_order');
        $result = $db->getObjects('menu_link');
        if (empty($result)) {
            return;
        }

        foreach ($result as $link) {
            $link->loadKey();
            $link->loadChildren();
            $this->_children[$link->id] = $link;
        }
    }

    function setParent($parent)
    {
        $this->parent = (int)$parent;
    }

    function setKeyId($key_id)
    {
        $this->key_id = (int)$key_id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags(trim($title));
    }

    function resetOrder()
    {
        $db = $this->getDB();

        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addOrder('link_order');
        $result = $db->getObjects('Menu_Link');
        if (empty($result)) {
            return;
        }
        $count = 1;

        foreach ($result as $link) {
            $link->link_order = $count;
            $link->save();
            $count++;
        }
    }


    function getUrl()
    {
        return $this->_key->url;
    }

    function setMenuId($id)
    {
        $this->menu_id = (int)$id;
    }

    function _getOrder()
    {
        $db = $this->getDB();
        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addColumn('link_order', FALSE, 'max');
        $current_order = $db->select('one');
        if (empty($current_order)) {
            $current_order = 1;
        } else {
            $current_order++;
        }

        return $current_order;
    }

    function save()
    {
        if (empty($this->menu_id) || empty($this->title) || empty($this->_key->url)) {
            return PHPWS_Error::get(MENU_MISSING_INFO, 'menu', 'Menu_Link::save');
        }

        if (empty($this->id) || empty($this->link_order)) {
            $this->link_order = $this->_getOrder();
        }

        $db = $this->getDB();
        return $db->saveObject($this);
    }

    function view($level='1')
    {
        static $current_parent = array();
        static $current_page = 0;

        $current_key = Key::getCurrent();

        if ($current_page < 1) {
            $child_current = $this->childIsCurrent($current_key);
            if ($child_current) {
                $current_parent[] = $this->id;
                $current_page = $child_current;
            } elseif ($current_key->id == $this->key_id) {
                $current_page = $this->id;
            }
        }

        if ($current_page == $this->id) {
                $current_parent[] = $this->id;
                $template['CURRENT_LINK'] = 'id="current-link"';
        }
 
        if ($this->id == $current_page ||
            $this->parent == 0         ||
            in_array($this->parent, $current_parent)) {

            PHPWS_Core::configRequireOnce('menu', 'config.php');

            $link = sprintf('<a href="%s" title="%s">%s</a>', $this->getUrl(), $this->title, $this->title);

            $this->_loadAdminLinks($template);

            $template['LINK'] = $link;
            if (!empty($this->_children)) {
                foreach ($this->_children as $kid) {
                    if ($kid_link = $kid->view($level+1)) {
                        $sublinks[] = $kid_link;
                    }
                }

                if (!empty($sublinks)) {
                    $template['SUBLINK'] = implode("\n", $sublinks);
                }
            }

            $template['LEVEL'] = $level;

            return PHPWS_Template::process($template, 'menu', 'links/link.tpl');
        } else {
            return NULL;
        }
    }

    function childIsCurrent(&$current_key)
    {
        if (empty($this->_children)) {
            return 0;
        }

        foreach ($this->_children as $child) {
            if ($child->key_id == $current_key->id) {
                return $child->id;
            }
        }
        return 0;

    }

    function _loadAdminLinks(&$template)
    {
        if ( empty($_POST) && Menu::isAdminMode() && Current_User::allow('menu') ) {
            $template['ADD_LINK'] = Menu::getAddLink($this->menu_id, $this->id);
            
            $vars['link_id'] = $this->id;

            $vars['command'] = 'delete_link';
            $js['QUESTION'] = _('Are you sure you want to delete this link: ' . addslashes($this->title));
            $js['ADDRESS'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $js['LINK'] = MENU_LINK_DELETE;
            $template['DELETE_LINK'] = javascript('confirm', $js);

            $vars['command'] = 'edit_link_title';
            $prompt_js['question'] = _('Type the new title for this link.');
            $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $prompt_js['answer'] = addslashes($this->title);
            $prompt_js['value_name'] = 'link_title';
            $prompt_js['link']       = MENU_LINK_EDIT;
            $template['EDIT_LINK'] = javascript('prompt', $prompt_js);

            $vars['command'] = 'move_link_up';
            $template['MOVE_LINK_UP'] = PHPWS_Text::secureLink(MENU_LINK_UP, 'menu', $vars);
            $vars['command'] = 'move_link_down';
            $template['MOVE_LINK_DOWN'] = PHPWS_Text::secureLink(MENU_LINK_DOWN, 'menu', $vars);

            $template['ADMIN'] = MENU_LINK_ADMIN;
        }
    }

    function delete($save_links=FALSE)
    {
        $db = $this->getDB();
        $db->addWhere('parent', $this->id);
        $result = $db->getObjects('Menu_Link');
        if (PEAR::isError($result)) {
            return $result;
        }
            
        $db->reset();
        $db->addWhere('id', $this->id);
        $db->delete();

        if (!empty($result)) {
            foreach ($result as $link) {
                if ($save_links) {
                    $link->setParent($this->parent);
                    $link->save();
                } else {
                    $link->delete();
                }
            }
        }
        
    }

    function moveUp()
    {
        if ($this->link_order == 1) {
            $this->link_order = $this->_getOrder();
            $this->save();
            $this->resetOrder();
            return TRUE;
        }

        $above = & new Menu_Link;

        $db = $this->getDB();
        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addWhere('link_order', $this->link_order - 1);
        $db->loadObject($above);
        $above->loadKey();

        $above->link_order = $this->link_order;
        $this->link_order--;
        $result = $above->save();

        if (PEAR::isError($result)) {
            return $result;
        }

        $this->save();
        return $this->save();
    }

    function moveDown()
    {
        $top_value = $this->_getOrder();
        if ($this->link_order == ($top_value - 1)) {
            $this->link_order = -1;
            $this->save();
            $this->resetOrder();
            return TRUE;
        }

        $below = & new Menu_Link;
        
        $db = $this->getDB();
        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addWhere('link_order', $this->link_order + 1);
        $db->loadObject($below);
        $below->loadKey();

        $below->link_order = $this->link_order;
        $this->link_order++;
        $result = $below->save();

        if (PEAR::isError($result)) {
            return $result;
        }

        return $this->save();
    }

}

?>