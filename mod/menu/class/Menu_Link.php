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
    public $id         = 0;
    public $menu_id    = 0;
    public $key_id     = NULL;
    public $title      = NULL;
    public $url        = NULL;
    public $parent     = 0;
    public $active     = 1;
    public $link_order = 1;
    public $_menu      = NULL;
    public $_error     = NULL;
    public $_children  = NULL;
    public $_db        = NULL;
    public $_key       = NULL;

    public function __construct($id=NULL)
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

    public function init()
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

    public function getDB()
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
    public function loadChildren()
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

    public function setParent($parent)
    {
        $this->parent = (int)$parent;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = (int)$key_id;
    }

    public function setTitle($title)
    {
        $title = strip_tags(trim($title));

        $char_limit = PHPWS_Settings::get('menu', 'max_link_characters');

        if ($char_limit > 0 && strlen($title) > $char_limit) {
            $title = substr($title, 0, $char_limit);
        }
        $this->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setUrl($url)
    {
        if (!preg_match('/^index.php/i', $url) && preg_match('/\w+\.\w{2,3}($|\/)/', $url)) {
            $url = PHPWS_Text::checkLink($url);
        }
        PHPWS_Text::makeRelative($url);
        $url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/i', '', $url);
    }

    public function getUrl()
    {
        return sprintf('<a href="%s" class="menu-link-href" id="menu-link-href-%s" title="%s">%s</a>', 
                       str_replace('&', '&amp;', $this->url), $this->id, $this->title, $this->title);
    }


    public function resetOrder()
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


    public function setMenuId($id)
    {
        $this->menu_id = (int)$id;
    }

    public function _getOrder()
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

    public function save()
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

    public function isCurrentUrl() {
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

    public function view($level='1')
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

        if ((Menu::isAdminMode() && PHPWS_Settings::get('menu', 'show_all_admin')) ||
            $this->_menu->_show_all || $current_link || $this->parent == 0 ||
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
            $template['ID'] = sprintf('menu-link-%s', $this->id);
            $template['PARENT_ID'] = sprintf('menu-parent-%s', $this->id);
            $tpl_file = 'menu_layout/' . $this->_menu->template . '/link.tpl';
            return PHPWS_Template::process($template, 'menu', $tpl_file);
        } else {
            return NULL;
        }
    }

    /**
     * Compares a link's children to the current key
     */
    public function childIsCurrent($current_key)
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

    public function childIsCurrentUrl()
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


    public function _loadAdminLinks(&$template, $popup=false)
    {
        if ( Menu::isAdminMode() && Current_User::allow('menu') ) {
            if (empty($_POST)) {
                $key = Key::getCurrent();

                if (Key::checkKey($key)) {
                    $keyed = true;
                } else {
                    $key = new Key;
                    $keyed = false;
                }

                $vars['link_id'] = $this->id;

                if ($popup || PHPWS_Settings::get('menu', 'float_mode')) {
                    $template['PIN_LINK']      = Menu_Item::getPinLink($this->menu_id, $this->id, $popup);
                    $template['ADD_LINK']      = Menu::getAddLink($this->menu_id, $this->id, $popup);
                    $template['ADD_SITE_LINK'] = Menu::getSiteLink($this->menu_id, $this->id, $keyed, $popup);
                    $template['DELETE_LINK']   = $this->deleteLink($popup);
                    $template['EDIT_LINK']     = $this->editLink($popup);

                    if (!PHPWS_Settings::get('menu', 'drag_sort')) {
                        $vars['command'] = 'move_link_up';
                        $up_link = MENU_LINK_UP;
                        if ($popup) {
                            $up_link .= ' ' . dgettext('menu', 'Move link up');
                            $vars['pu'] = 1;
                            $template['MOVE_LINK_UP'] = PHPWS_Text::secureLink($up_link, 'menu', $vars);
                        } else {
                            $template['MOVE_LINK_UP'] = sprintf('<a style="cursor : pointer" onclick="move_link(\'%s\', \'%s\', \'%s\')">%s</a>',
                                                                $this->menu_id, $this->id, 'up', $up_link);
                        }
                        
                        $down_link = MENU_LINK_DOWN;
                        $vars['command'] = 'move_link_down';
                        if ($popup) {
                            $down_link .= ' ' . dgettext('menu', 'Move link down');
                            $vars['pu'] = 1;
                            $template['MOVE_LINK_DOWN'] = PHPWS_Text::secureLink($down_link, 'menu', $vars);
                        } else {
                            $template['MOVE_LINK_DOWN'] = sprintf('<a style="cursor : pointer" onclick="move_link(\'%s\', \'%s\', \'%s\')">%s</a>',
                                                                  $this->menu_id, $this->id, 'down', $down_link);
                        }
                    } elseif ($this->link_order != 1) {
                        $template['MOVE_LINK_UP'] = sprintf('<a style="cursor : pointer" id="menu-indent-%s-%s" class="menu-indent">%s</a>',
                                                            $this->menu_id, $this->id, MENU_LINK_INDENT);
                    }
                    $template['ADMIN'] = MENU_LINK_ADMIN;
                } else {

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
                }
            } else {
                $template['ADMIN'] = NO_POST;
            }
        }
    }

    public function editLink($popup=false)
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
            $prompt_js['answer']     = $this->title;
            $prompt_js['value_name'] = 'link_title';
            $prompt_js['link']       = $link;
            return javascript('prompt', $prompt_js);
        } else {
            $vars['command'] = 'edit_link';
            $prompt_js['address'] = PHPWS_Text::linkAddress('menu', $vars, true);
            $prompt_js['label']   = $link;
            $prompt_js['width']   = 500;
            $prompt_js['height']  = 300;
            return javascript('open_window', $prompt_js);
        }
    }


    public function deleteLink($popup=false)
    {
        $link = MENU_LINK_DELETE;

        if (!$popup) {
            return sprintf('<a style="cursor : pointer" onclick="delete_link(\'%s\', \'%s\', \'%s\')">%s</a>',
                           $this->menu_id, $this->id, htmlentities($this->getTitle(), ENT_QUOTES, 'UTF-8'), $link);
        } else {
            $link .= ' ' . dgettext('menu', 'Delete link');
            $vars['pu'] = 1;
        }

        $js['LINK'] = & $link;

        $vars['link_id'] = $this->id;
        $vars['command'] = 'delete_link';
        $js['QUESTION'] = dgettext('menu', 'Are you sure you want to delete this link: ' .
                                   addslashes($this->getTitle()));
        $js['ADDRESS'] = PHPWS_Text::linkAddress('menu', $vars, true);
        return javascript('confirm', $js);
    }

    public function delete($save_links=false)
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

    public function moveUp()
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

    public function moveDown()
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