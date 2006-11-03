<?php
  /**
   * Class for individual menu links
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('MENU_MISSING_INFO', 1);

class Menu_Link {
    var $id         = 0;
    var $menu_id    = 0;
    var $key_id     = NULL;
    var $title      = NULL;
    var $url        = NULL;
    var $parent     = 0;
    var $active     = 1;
    var $link_order = 1;
    var $_menu      = NULL;
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
    }

    function &getDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('menu_links');
        }
        $this->_db->reset();
        return $this->_db;
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
        if (MENU_TITLE_LIMIT > 0 && strlen($this->title) > MENU_TITLE_LIMIT) {
            $this->title = substr($this->title, 0, MENU_TITLE_LIMIT);
        }
    }

    function setUrl($url)
    {
        if (!preg_match('/^index\.php/', $url)) {
            $url = PHPWS_Text::checkLink($url);
            PHPWS_Text::makeRelative($url);
        }

        $this->url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/i', '', $url);
    }

    function getUrl()
    {
        return sprintf('<a href="%s" title="%s">%s</a>', str_replace('&', '&amp;', $this->url), $this->title, $this->title);
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


    function setMenuId($id)
    {
        $this->menu_id = (int)$id;
    }

    function _getOrder()
    {
        $db = $this->getDB();
        $db->addWhere('menu_id', $this->menu_id);
        $db->addWhere('parent', $this->parent);
        $db->addColumn('link_order', 'max');
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
        if (empty($this->menu_id) || empty($this->title) || 
            empty($this->url) || !isset($this->key_id) ) {
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
        $current_link = FALSE;

        $current_key = Key::getCurrent();

        if (!empty($current_key)) {
            if ($this->childIsCurrent($current_key)) {
                $current_parent[] = $this->id;
            }

            if ( (!$current_key->isDummy() && $current_key->id == $this->key_id) || ($current_key->url == $this->url) ) {
                $current_link = TRUE;
                $current_parent[] = $this->id;
                $template['CURRENT_LINK'] = MENU_CURRENT_LINK_STYLE;
            }
        }

 
        if ($current_link || $this->parent == 0         ||
            in_array($this->parent, $current_parent)) {

            $link = $this->getUrl();

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
            return FALSE;
        }

        foreach ($this->_children as $child) {
            if ( ($current_key->id !== 0 && $child->key_id == $current_key->id) || ($child->url == $current_key->url) ){
                return TRUE;
            }
        }
        return FALSE;
    }


    function _loadAdminLinks(&$template)
    {
        if ( empty($_POST) && Menu::isAdminMode() && Current_User::allow('menu') ) {
            $template['ADD_LINK'] = Menu::getAddLink($this->menu_id, $this->id);
            $template['ADD_SITE_LINK'] = Menu::getSiteLink($this->menu_id, $this->id);
            
            $vars['link_id'] = $this->id;

            $vars['command'] = 'delete_link';
            $js['QUESTION'] = _('Are you sure you want to delete this link: ' . addslashes($this->title));
            $js['ADDRESS'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
            $js['LINK'] = MENU_LINK_DELETE;
            $template['DELETE_LINK'] = javascript('confirm', $js);

            if ($this->key_id) {
                $vars['command'] = 'edit_link_title';
                $prompt_js['question'] = _('Type the new title for this link.');
                $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
                $prompt_js['answer'] = addslashes($this->title);
                $prompt_js['value_name'] = 'link_title';
                $prompt_js['link']       = MENU_LINK_EDIT;
                $template['EDIT_LINK'] = javascript('prompt', $prompt_js);
            } else {
                $vars['command'] = 'edit_link';
                $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, TRUE);
                $prompt_js['label']   = MENU_LINK_EDIT;
                $prompt_js['width']   = 500;
                $prompt_js['height']  = 200;
                $template['EDIT_LINK'] = javascript('open_window', $prompt_js);
            }

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
        $db->addWhere('id', $this->id);
        $db->delete();
        $db->reset();

        $db->addWhere('parent', $this->id);
        if ($save_links) {
            $db->addValue('parent', $this->parent);
            return $db->update();
        } else {
            return $db->delete();
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