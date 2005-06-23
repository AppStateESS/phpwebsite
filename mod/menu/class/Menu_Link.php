<?php

define('MENU_MISSING_INFO', 1);

class Menu_Link {
    var $id         = 0;
    var $menu_id    = 0;
    var $title      = NULL;
    var $url        = NULL;
    var $parent     = 0;
    var $active     = 1;
    var $link_order = 1;
    var $_error     = NULL;
    var $_children  = NULL;
    var $_db        = NULL;

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
        $this->_db = & new PHPWS_DB('menu_links');
        $this->_db->loadObject($this);
        $this->loadChildren();
    }

    function &getDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('menu_links');
        }
        return $this->_db;
    }

    function loadChildren()
    {
        $db = $this->getDB();
        $db->reset();
        $db->addWhere('parent', $this->id);
        $db->addOrder('link_order');
        $result = $db->getObjects('menu_link');
        if (empty($result)) {
            return;
        }

        foreach ($result as $link) {
            $link->loadChildren();
            $this->_children[$link->id] = $link;
        }
    }

    function setParent($parent)
    {
        $this->parent = (int)$parent;
    }

    function setTitle($title)
    {
        $this->title = strip_tags(trim($title));
    }

    function setUrl($url, $local=TRUE)
    {
        if ($local) {
            PHPWS_Text::makeRelative($url);
        }
        $this->url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/', '', $this->url);
    }

    function getUrl()
    {
        return str_replace('&', '&amp;', $this->url);
    }

    function setMenuId($id)
    {
        $this->menu_id = (int)$id;
    }

    function _getOrder()
    {
        $db = $this->getDB();
        $db->reset();
        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addColumn('link_order');
        $current_order = $db->select('max');

        if (empty($current_order)) {
            $current_order = 1;
        } else {
            $current_order++;
        }

        return $current_order;
    }

    function save()
    {
        if (empty($this->menu_id) || empty($this->title) || empty($this->url)) {
            return PHPWS_Error::get(MENU_MISSING_INFO, 'menu', 'Menu_Link::save');
        }
        
        $this->link_order = $this->_getOrder();

        $this->_db->reset();
        return $this->_db->saveObject($this);
    }

    function view()
    {
        if (Menu::atLink($this->url)) {
            $GLOBALS['Menu_Current_Parent'][] = $this->parent;
        }

        PHPWS_Core::configRequireOnce('menu', 'config.php');

        $link = sprintf('<a href="%s" title="%s">%s</a>', $this->getUrl(), $this->title, $this->title);

        if ( Menu::isAdminMode() && Current_User::allow('menu') ) {
            $template['ADD_LINK'] = Menu::getAddLink($this->menu_id, $this->id);

            $vars['link_id'] = $this->id;

            $vars['command'] = 'delete_link';
            $js['QUESTION'] = _('Are you sure you want to delete this link: ' . $this->title);
            $js['ADDRESS'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $js['LINK'] = MENU_LINK_DELETE;
            $template['DELETE_LINK'] = javascript('confirm', $js);

            $vars['command'] = 'edit_link_title';
            $prompt_js['question'] = _('Type the new title for this link.');
            $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $prompt_js['answer'] = $this->title;
            $prompt_js['value_name'] = 'link_title';
            $prompt_js['link']       = MENU_LINK_EDIT;
            $template['EDIT_LINK'] = javascript('prompt', $prompt_js);

            $vars['command'] = 'move_link_up';
            $template['MOVE_LINK_UP'] = PHPWS_Text::secureLink(MENU_LINK_UP, 'menu', $vars);
            $vars['command'] = 'move_link_down';
            $template['MOVE_LINK_DOWN'] = PHPWS_Text::secureLink(MENU_LINK_DOWN, 'menu', $vars);
            $template['ADMIN'] = MENU_LINK_ADMIN;
        }

        if ($this->parent == 0 ||
            (isset($GLOBALS['Menu_Current_Parent']) && in_array($this->parent, $GLOBALS['Menu_Current_Parent']))) {
            $template['STATUS'] = 'show-link';
        } else {
            $template['STATUS'] = 'hide-link';
        }

        $template['LINK'] = $link;
        if (!empty($this->_children)) {
            foreach ($this->_children as $kid) {
                $sublinks[] = $kid->view();
            }
            $template['SUBLINK'] = implode("\n", $sublinks);
        }

        return PHPWS_Template::process($template, 'menu', 'links/link.tpl');
    }

    function delete($save_links=FALSE)
    {
        $db = $this->getDB();
        $db->reset();
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

}

?>