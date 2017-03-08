<?php

/**
 * Class for individual menu links
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
define('MENU_MISSING_INFO', 1);

class Menu_Link {

    public $id = 0;
    public $menu_id = 0;
    public $key_id = NULL;
    public $title = NULL;
    public $url = NULL;
    public $parent = 0;
    public $active = 1;
    public $link_order = 1;
    public $_menu = NULL;
    public $_error = NULL;
    public $_children = NULL;
    public $_db = NULL;
    public $_key = NULL;

    public function __construct($id = NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int) $id;
        $result = $this->init();
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
            $this->id = 0;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('menu_links');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
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
        $this->_db = new PHPWS_DB('menu_links');
        return $this->_db;
    }

    /**
     * Grabs all the child links under the current link
     */
    public function loadChildren($data = null, $hash = null)
    {
        // If we're doing this the old, inefficient way...
        if (empty($data) || empty($hash)) {
            $db = $this->getDB();
            $db->addWhere('parent', $this->id);
            $db->addOrder('link_order');
            \Canopy\Key::restrictView($db);
            $result = $db->getObjects('menu_link');
            if (empty($result)) {
                return;
            }

            foreach ($result as $link) {
                $link->loadChildren();
                $this->_children[$link->id] = $link;
            }
        }
        // otherwise, if this link has no children...
        elseif (empty($hash[$this->id])) {
            return;
        } else {
            foreach ($hash[$this->id] as $rowId) {
                $link = new Menu_Link();
                \phpws\PHPWS_Core::plugObject($link, $data[$rowId]);
                $link->loadChildren($data, $hash);
                $this->_children[$link->id] = $link;
            }
        }
    }

    public function setParent($parent)
    {
        $this->parent = (int) $parent;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = (int) $key_id;
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
        if (!preg_match('/^(\.\/|index.php)/i', $url) && preg_match('/\w+\.\w{2,3}($|\/)/',
                        $url)) {
            $url = PHPWS_Text::checkLink($url);
        }
        PHPWS_Text::makeRelative($url);
        $url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/i', '', $url);
    }

    /**
     * Returns the fully-formed html anchor tag (<a href="..." ...> ... </a>) for this link.
     *
     * @return String Html anchor tag for this link.
     */
    public function getAnchorTag($admin = false)
    {
        if ($admin) {
            $data = ' data-link-id="' . $this->id . '" data-key-id="' . $this->key_id . '"';
        } else {
            $data = null;
        }
        $icon = null;
        if (\PHPWS_Settings::get('menu', 'link_icons')) {
            if (preg_match('/^https?:/', $this->url)) {
                $icon = '<i class="fa fa-external-link fa-fw" style="font-size : 80%"></i>';
            } elseif (preg_match('/(\.pdf|filecabinet\/\d+)$/', $this->url)) {
                $icon = '<i class="fa fa-file-pdf-o fa-fw" style="font-size : 80%"></i>';
            }
        }
        
        if (preg_match('/^https?:/', $this->url)) {
        	$target = "_blank";
        } else {
        	$target = "_self";
        }
        
        return sprintf('<a href="%s" class="menu-link-href"%s id="menu-link-href-%s" title="%s" target="%s">%s %s</a>',
                str_replace('&', '&amp;', $this->url), $data, $this->id,
                $this->title, $target, $this->title, $icon);
    }

    /**
     * Returns the URL for this link.
     *
     * @return String URL for this link
     */
    public function getUrl()
    {
        return $this->url;
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
        $this->menu_id = (int) $id;
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
                empty($this->url) || !isset($this->key_id)) {
            throw new \Exception('Link is missing a title, url, or menu id');
        }

        if (empty($this->id) || empty($this->link_order)) {
            $this->link_order = $this->_getOrder();
        }
        $db = $this->getDB();
        $result = $db->saveObject($this);
        return $result;
    }

    public function isCurrentUrl()
    {
        static $current_url = null;
        static $redirect_url = null;

        /**
         * If the current link's url starts with http and does not match
         * the current home address, return false because it is an offsite link.
         */
        $home = preg_quote(PHPWS_HOME_HTTP);
        if (preg_match('@^http@i', $this->url) && !preg_match("@$home@i",
                        $this->url)) {
            return false;
        }

        if (!$current_url) {
            $current_url = preg_quote(\phpws\PHPWS_Core::getCurrentUrl(true, false));
        }

        if (!$redirect_url) {
            $redirect_url = preg_quote(\phpws\PHPWS_Core::getCurrentUrl());
        }

        if (preg_match('#' . $redirect_url . '$#', $this->url)) {
            return true;
        } else {
            return false;
        }
    }

    public function view($level = '1', $admin = false)
    {
        \PHPWS_Core::requireConfig('menu');
        static $current_parent = array();

        $current_link = false;
        $current_key = \Canopy\Key::getCurrent();
        if (!empty($current_key)) {
            if ($this->childIsCurrent($current_key)) {
                $current_parent[] = $this->id;
            }

            if ((!$current_key->isDummy() && $current_key->id == $this->key_id) || ($current_key->url == $this->url)) {
                $current_link = true;
                $current_parent[] = $this->id;
                $template['CURRENT_LINK'] = MENU_CURRENT_LINK_STYLE;
                $template['ACTIVE'] = 'active'; // booststrap theme
            }
        }
        if (!isset($template['CURRENT_LINK']) && $this->isCurrentUrl()) {
            $current_link = true;
            $current_parent[] = $this->id;
            $template['CURRENT_LINK'] = MENU_CURRENT_LINK_STYLE;
            $template['ACTIVE'] = 'active'; // booststrap theme
        }

        if ($this->childIsCurrentUrl()) {
            $current_parent[] = $this->id;
        }

        if ($this->_menu->_show_all || $current_link || $this->parent == 0 ||
                in_array($this->parent, $current_parent)) {
            $link = $this->getAnchorTag($admin);

            $template['LINK'] = $link;
            $template['LINK_URL'] = $this->url;
            $template['LINK_DROPDOWN'] = 'dropdown'; // Dummy tag to make dropdowns work
            $template['LINK_TEXT'] = $this->title;
            if (!empty($this->_children)) {
                foreach ($this->_children as $kid) {
                    $kid->_menu = & $this->_menu;
                    if ($kid_link = $kid->view($level + 1, $admin)) {
                        $sublinks[] = $kid_link;
                    }
                }

                if (!empty($sublinks)) {
                    $template['SUBLINK'] = implode("\n", $sublinks);
                }
                $template['PARENT_ID'] = sprintf('menu-parent-%s', $this->id);
            }

            $template['LEVEL'] = $level;
            $template['ID'] = $this->id;
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
            if (($current_key->id !== 0 && $child->key_id == $current_key->id) ||
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
            if ($child->isCurrentUrl()) {
                return true;
            }

            if (!empty($child->_children)) {
                if ($child->childIsCurrentUrl()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function delete($save_links = false)
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

}
