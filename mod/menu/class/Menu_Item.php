<?php
/**
 * Object class for a menu
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('menu', 'Menu_Link.php');

class Menu_Item {
    var $id         = 0;
    var $title      = NULL;
    var $template   = NULL;
    var $pin_all    = 0;
    var $_db        = NULL;
    var $_error     = NULL;

    function Menu_Item($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        $this->resetdb();
        if (PEAR::isError($result)) {
            $this->_error = $result;
            PHPWS_Error::log($result);
        }
    }

    function resetdb()
    {
        if (isset($this->_db)) {
            $this->_db->reset();
        } else {
            $this->_db = & new PHPWS_DB('menus');
        }
    }

    function init()
    {
        if (!isset($this->id)) {
            return FALSE;
        }

        $this->resetdb();
        $result = $this->_db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setTemplate($template)
    {
        $this->template = $template;
    }

    function setPinAll($pin)
    {
        $this->pin_all = (bool)$pin;
    }
    

    function getTemplateList()
    {
        $result = PHPWS_Template::listTemplates('menu', 'menu_layout');
        return $result;
    }

    function post()
    {
        if (empty($_POST['title'])) {
            $errors[] = _('Missing menu title.');
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setTemplate($_POST['template']);

        if (isset($_POST['pin_all'])) {
            $this->setPinAll(1);
        } else {
            $this->setPinAll(0);
        }

        if (isset($errors)) {
            return $errors;
        } else {
            $result = $this->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return array(_('Unable to save menu. Please check error logs.'));
            }
            return TRUE;
        }
    }

    function save()
    {
        if (empty($this->title)) {
            return FALSE;
        }

        $this->resetdb();
        return $this->_db->saveObject($this);
    }

    /**
     * Returns all the links in a menu for display
     */
    function displayLinks($edit=FALSE)
    {
        $all_links = $this->getLinks();

        if (empty($all_links)) {
            return NULL;
        }

        foreach ($all_links as $link) {
            $link_list[] = $link->view();
        }

        return implode("\n", $link_list);
    }

    /**
     * Returns the menu link objects associated to a menu
     */
    function getLinks($parent=0, $active_only=TRUE)
    {
        $final = NULL;

        // If we have been here already, return the data
        if (isset($GLOBALS['MENU_LINKS'][$this->id])) {
            return $GLOBALS['MENU_LINKS'][$this->id];
        }

        if (!$this->id) {
            return NULL;
        }

        $db = & new PHPWS_DB('menu_links');
        $db->addWhere('menu_id', $this->id, NULL, NULL, 1);
        $db->addWhere('parent', $parent, NULL, NULL, 1);

        Key::restrictView($db, 'menu');
        $db->addOrder('link_order');

        $db->setIndexBy('id');

        //$db->setTestMode();
        $result = $db->getObjects('menu_link');

        if (empty($result)) {
            return NULL;
        }

        foreach ($result as $link) {
            $link->loadChildren();
            $final[$link->id] = $link;
        }

        $GLOBALS['MENU_LINKS'][$this->id] = $final;

        return $final;
    }



    function getRowTags()
    {
        $vars['menu_id'] = $this->id;
        $vars['command'] = 'edit_menu';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'menu', $vars);

        if (!isset($_SESSION['Menu_Clip']) || 
            !isset($_SESSION['Menu_Clip'][$this->id])) {
            $vars['command'] = 'clip';
            $links[] = PHPWS_Text::secureLink(_('Clip'), 'menu', $vars);
        } else {
            $vars['command'] = 'unclip';
            $links[] = PHPWS_Text::secureLink(_('Unclip'), 'menu', $vars);
        }

        $vars['command'] = 'pin_all';
        if ($this->pin_all == 0) {
            $link_title = _('Pin');
            $vars['hook'] = 1;
        } else {
            $link_title = _('Unpin');
            $vars['hook'] = 0;
        }
        $links[] = PHPWS_Text::secureLink($link_title, 'menu', $vars);
        unset($vars['hook']);

        $vars['command'] = 'delete_menu';
        $js['QUESTION'] = _('Are you sure you want to delete this menu and all its links.');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('menu', $vars, TRUE);
        $js['LINK'] = _('Delete');
        $links[] = javascript('confirm', $js);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function kill()
    {
        $db = & new PHPWS_DB('menu_assoc');
        $db->addWhere('menu_id', $this->id);
        $db->delete();

        $db->setTable('menu_links');
        $db->delete();

        $db->setTable('menus');
        $db->reset();
        $db->addWhere('id', $this->id);
        $db->delete();

        Layout::purgeBox('menu_' . $this->id);
    }

    function addRawLink($title, $url, $parent=0)
    {
        if (empty($title) || empty($url)) {
            return FALSE;
        }

        $link = & new Menu_Link;
        $link->key_id = 0;
        $link->setMenuId($this->id);
        $link->setTitle($title);
        $link->setUrl(urldecode($url));
        $link->setParent($parent);

        return $link->save();
    }

    function addLink($key_id, $parent=0)
    {
        $key = & new Key($key_id);
        $link = & new Menu_Link;

        $link->setMenuId($this->id);
        $link->setKeyId($key->id);
        $link->setTitle($key->title);
        $link->setUrl($key->url);
        $link->setParent($parent);

        return $link->save();
    }

    /**
     * Returns a menu and its links for display
     */
    function view($pin_mode=FALSE)
    {
        $key = Key::getCurrent();

        if ($pin_mode && $key->isDummy(true)) {
            return;
        }

        $edit = FALSE;
        $file = 'menu_layout/' . $this->template;
        $content_var = 'menu_' . $this->id;

        if ( !$pin_mode && Current_User::allow('menu') ) {
            if (Menu::isAdminMode()) {
                $tpl['ADD_LINK'] = Menu::getAddLink($this->id);
                $tpl['ADD_OFFSITE_LINK'] = Menu::getOffsiteLink($this->id);

                if (!empty($key)) {
                    $tpl['CLIP'] = Menu::getUnpinLink($this->id, $key->id, $this->pin_all);
                } else {
                    $tpl['CLIP'] = Menu::getUnpinLink($this->id, -1, $this->pin_all);
                }

                $vars['command'] = 'disable_admin_mode';
                $vars['return'] = 1;
                $tpl['ADMIN_LINK'] = PHPWS_Text::moduleLink(MENU_ADMIN_OFF, 'menu', $vars);
            } else {
                $vars['command'] = 'enable_admin_mode';
                $vars['return'] = 1;
                $tpl['ADMIN_LINK'] = PHPWS_Text::moduleLink(MENU_ADMIN_ON, 'menu', $vars);
            }
        }

        $tpl['TITLE'] = $this->title;
        $tpl['LINKS'] = $this->displayLinks($edit);

        if ($pin_mode &&
            Current_User::allow('menu') && 
            isset($_SESSION['Menu_Clip']) && 
            isset($_SESSION['Menu_Clip'][$this->id])) {

            $pinvars['command'] = 'pin_menu';
            $pinvars['key_id'] = $key->id;
            $pinvars['menu_id'] = $this->id;
            $tpl['CLIP'] = PHPWS_Text::secureLink(MENU_PIN, 'menu', $pinvars);
        }

        $content = PHPWS_Template::process($tpl, 'menu', $file);
        Layout::set($content, 'menu', $content_var);
    }

}

?>