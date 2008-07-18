<?php
  /**
   * Class for individual menu links
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('MENU_MISSING_INFO', 1);

if (!defined('NO_POST')) {
    define('NO_POST', '');
}

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
            $this->id = 0;
        }
    }

    function init()
    {
        $db = $this->getDB();
        $db = new PHPWS_DB('menu_links');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$result) {
            $this->id = 0;
            return false;
        }
        $this->loadChildren();
    }

    function getDB()
        {
            if (empty($this->_db)) {
                $this->_db = new PHPWS_DB('menu_links');
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
        Key::restrictView($db);
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
        $title = strip_tags(trim($title));

        $char_limit = PHPWS_Settings::get('menu', 'max_link_characters');

        if ($char_limit > 0 && strlen($title) > $char_limit) {
            $title = substr($title, 0, $char_limit);
        }
        $this->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
    }

    function getTitle()
    {
        return PHPWS_Text::decodeText($this->title);
    }

    function setUrl($url)
    {
        if (!preg_match('/^index.php/i', $url) && preg_match('/\w+\.\w{2,3}($|\/)/', $url)) {
            $url = PHPWS_Text::checkLink($url);
        }
        PHPWS_Text::makeRelative($url);
        $url = str_replace('&amp;', '&', trim($url));
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

    function isCurrentUrl() {
        static $current_url = null;
        static $redirect_url = null;

        if (!$current_url) {
            $current_url = preg_quote(PHPWS_Core::getCurrentUrl(true,false));
        }

        if (!$redirect_url) {
            $redirect_url = preg_quote(PHPWS_Core::getCurrentUrl());
        }

        if ( preg_match("@$current_url$@", $this->url) ||
             preg_match("@$redirect_url$@", $this->url) ) {
            return true;
        } else {
            return false;
        }
    }

    function view($level='1')
    {
        static $current_parent = array();

        $current_link = false;
        $current_key = Key::getCurrent();

        if (!empty($current_key)) {
            if ($this->childIsCurrent($current_key)) {
                $current_parent[] = $this->id;
            }
            if ( (!$current_key->isDummy() && $current_key->id == $this->key_id) || ($current_key->url == $this->url) ) {
                $current_link = true;
                $current_parent[] = $this->id;
                $template['CURRENT_LINK'] = MENU_CURRENT_LINK_STYLE;
            }
        } else {
            if ($this->isCurrentUrl()) {
                $current_link = true;
                $current_parent[] = $this->id;
                $template['CURRENT_LINK'] = MENU_CURRENT_LINK_STYLE;
            }

        }

        if ($this->childIsCurrentUrl()) {
            $current_parent[] = $this->id;
        }

        if ($this->_menu->_show_all || $current_link || $this->parent == 0 ||
            in_array($this->parent, $current_parent)) {

            $link = $this->getUrl();
            $this->_loadAdminLinks($template);

            $template['LINK'] = $link;
            if (!empty($this->_children)) {
                foreach ($this->_children as $kid) {
                    $kid->_menu = & $this->_menu;
                    if ($kid_link = $kid->view($level+1)) {
                        $sublinks[] = $kid_link;
                    }
                }

                if (!empty($sublinks)) {
                    $template['SUBLINK'] = implode("\n", $sublinks);
                }
            }

            $template['LEVEL'] = $level;
            $tpl_file = 'menu_layout/' . $this->_menu->template . '/link.tpl';
            return PHPWS_Template::process($template, 'menu', $tpl_file);
        } else {
            return NULL;
        }
    }

    /**
     * Compares a link's children to the current key
     */
    function childIsCurrent($current_key)
    {
        if (empty($this->_children)) {
            return false;
        }

        foreach ($this->_children as $child) {
            if ( ($current_key->id !== 0 && $child->key_id == $current_key->id) ||
                 ($child->url == $current_key->url)) {
                return true;
            }

            if (!empty($child->_children)) {
                if ($child->childIsCurrent($current_key)) {
                    return true;
                }
            }
        }
        return false;
    }

    function childIsCurrentUrl()
    {
        if (empty($this->_children)) {
            return false;
        }

        foreach ($this->_children as $child) {
            if  ($child->isCurrentUrl()) {
                return true;
            }

            if (!empty($child->_children)) {
                if ($child->childIsCurrentUrl()) {
                    return true;
                }
            }
        }
    }


    function _loadAdminLinks(&$template, $popup=false)
    {
        if ( Menu::isAdminMode() && Current_User::allow('menu') ) {
            if (empty($_POST)) {
                $key = Key::getCurrent();

                if (Key::checkKey($key)) {
                    $keyed = true;
                } else {
                    $keyed = false;
                }

                $vars['link_id'] = $this->id;

                if ($popup || PHPWS_Settings::get('menu', 'float_mode')) {
                    $template['PIN_LINK']      = Menu_Item::getPinLink($this->menu_id, $this->id, $popup);
                    $template['ADD_LINK']      = Menu::getAddLink($this->menu_id, $this->id, $popup);
                    $template['ADD_SITE_LINK'] = Menu::getSiteLink($this->menu_id, $this->id, $keyed, $popup);
                    $template['EDIT_LINK']     = $this->editLink($popup);
                    $template['DELETE_LINK']   = $this->deleteLink($popup);

                    $vars['command'] = 'move_link_up';
                    $up_link = MENU_LINK_UP;
                    if ($popup) {
                        $up_link .= ' ' . dgettext('menu', 'Move link up');
                        $vars['pu'] = 1;
                    }
                    $template['MOVE_LINK_UP'] = PHPWS_Text::secureLink($up_link, 'menu', $vars);

                    $down_link = MENU_LINK_DOWN;
                    if ($popup) {
                        $down_link .= ' ' . dgettext('menu', 'Move link down');
                        $vars['pu'] = 1;
                    }
                    $vars['command'] = 'move_link_down';
                    $template['MOVE_LINK_DOWN'] = PHPWS_Text::secureLink($down_link, 'menu', $vars);
                }

                $vars['command'] = 'popup_admin';
                $vars['curl'] = urlencode(PHPWS_Core::getCurrentUrl(false));
                if ($keyed) {
                    $vars['key_id'] = $key->id;
                }

                $js['address'] = PHPWS_Text::linkAddress('menu', $vars, true);
                $js['label'] = MENU_LINK_ADMIN;
                $js['width'] = 200;
                $js['height'] = 300;

                $template['ADMIN'] = javascript('open_window', $js);
            } else {
                $template['ADMIN'] = NO_POST;
            }
        }
    }

    function editLink($popup=false)
    {
        $vars['link_id'] = $this->id;
        $link = MENU_LINK_EDIT;
        if ($popup) {
            $link .= ' ' . dgettext('menu', 'Edit link');
            $vars['pu'] = 1;
        }

        if ($this->key_id) {
            $vars['command'] = 'edit_link_title';
            $prompt_js['question']   = dgettext('menu', 'Type the new title for this link.');
            $prompt_js['address']    = PHPWS_Text::linkAddress('menu', $vars, true);
            $prompt_js['answer']     = addslashes($this->getTitle());
            $prompt_js['value_name'] = 'link_title';
            $prompt_js['link']       = $link;
            return javascript('prompt', $prompt_js);
        } else {
            $vars['command'] = 'edit_link';
            $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, true);
            $prompt_js['label']   = $link;
            $prompt_js['width']   = 425;
            $prompt_js['height']  = 225;
            return javascript('open_window', $prompt_js);
        }
    }


    function deleteLink($popup=false)
    {
        $js['LINK'] = MENU_LINK_DELETE;
        if ($popup) {
            $js['LINK'] .= ' ' . dgettext('menu', 'Delete link');
            $vars['pu'] = 1;
        }

        $vars['link_id'] = $this->id;
        $vars['command'] = 'delete_link';
        $js['QUESTION'] = dgettext('menu', 'Are you sure you want to delete this link: ' .
                                   addslashes($this->getTitle()));
        $js['ADDRESS'] = PHPWS_Text::linkAddress('menu', $vars, true);
        return javascript('confirm', $js);
    }

    function delete($save_links=false)
    {
        $db = $this->getDB();
        $db->addWhere('id', $this->id);
        $db->delete();
        $db->reset();

        $menu = new Menu_Item($this->menu_id);
        $menu->reorderLinks();

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
            return true;
        }

        $above = new Menu_Link;

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

        return $this->save();
    }

    function moveDown()
    {
        $top_value = $this->_getOrder();
        if ($this->link_order == ($top_value - 1)) {
            $this->link_order = -1;
            $this->save();
            $this->resetOrder();
            return true;
        }

        $below = new Menu_Link;

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