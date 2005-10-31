<?php

/**
 * Small class to help modules plug in features from other modules
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_REQUEST['module'])) {
    $GLOBALS['PHPWS_Key'] = Key::getHomeKey();
} else {
    $GLOBALS['PHPWS_Key'] = NULL;
}

class Key {
    var $id              = 0;
    var $module          = NULL;
    var $item_name       = NULL;
    var $item_id         = NULL;
    var $title           = NULL;
    var $summary         = NULL;
    var $url             = NULL;
    var $active          = 1;

    // if TRUE/1 then only logged in users will access
    var $restricted      = 0;

    var $create_date     = 0;
    var $update_date     = 0;

    // contains permission allow name for viewing
    var $view_permission = NULL;

    // contains permission allow name for editing
    var $edit_permission = NULL;

    var $times_viewed    = 0;

    var $_error          = NULL;
  
    function Key($id=NULL)
    {

        if (!isset($id)) {
            return NULL;
        }

        if ((int)$id == 0) {
            $this->id = 0;
            $this->module = $this->item_name = 'home';
            $this->item_id = 0;
            $this->setTitle(_('Home'));
            $this->setUrl('index.php');
            return;
        }


        $this->id = (int)$id;
        $this->init();
    }

    function isKey($key)
    {
        if (is_object($key) && strtolower(get_class($key)) == 'key') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * returns the url in a link
     */
    function getUrl()
    {
        return sprintf('<a href="%s">%s</a>', $this->url, $this->title);
    }

    function setRestricted($restrict)
    {
        $this->restricted = (int)$restrict;
    }

    // restricted means that only logged users can access
    function isRestricted()
    {
        return (bool)$this->restricted;
    }

    function setViewPermission($permission)
    {
        if (empty($permission)) {
            $this->view_permission = NULL;
        } else {
            $this->restricted = 1;
            $this->view_permission = strip_tags($permission);
        }
    }

    function setEditPermission($permission)
    {
        if (empty($permission)) {
            $this->edit_permission = NULL;
        } else {
            $this->edit_permission = strip_tags($permission);
        }
    }

    function allowView()
    {
        if (!$this->restricted) {
            return TRUE;
        } else {
            if (empty($this->view_permissions)) {
                return Current_User::isLogged();
            } else {
                return Current_User::allow($this->module, $this->view_permission,
                                           $this->item_id, $this->item_name);                
            }
        }

        return TRUE;
    }

    function allowEdit()
    {
        if (empty($this->edit_permission)) {
            return TRUE;
        }

        return Current_User::allow($this->module, $this->edit_permission,
                                   $this->item_id, $this->item_name);
    }

    function init()
    {
        $db = & new PHPWS_DB('phpws_key');

        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->_error = $result;
        } elseif (empty($result)) {
            $this->_error = PHPWS_Error::get(KEY_NOT_FOUND, 'core', 'Key::init', $this->id);
            $this->id = NULL;
        }
        return $result;
    }

    function save()
    {
        // No need to save Home keys
        if ($this->isHomeKey()) {
            return TRUE;
        }

        if (empty($this->module) || empty($this->item_id)
            ) {
            return false;
        }
        
        if (empty($this->item_name) || $this->item_name == 'home') {
            $this->item_name = $this->module;
        }

        if (empty($this->create_date)) {
            $this->create_date = mktime();
        }

        $this->update_date = mktime();

        $db = & new PHPWS_DB('phpws_key');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }
        return TRUE;
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function setItemName($item_name)
    {
        $this->item_name = $item_name;
    }

    function setItemId($item_id)
    {
        $this->item_id = $item_id;

    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setSummary($summary)
    {
        $summary = preg_replace('/(<|&lt;|\[).*(>|&gt;|\])/sUi', ' ', $summary);
        $this->summary = $summary;
    }

    function setUrl($url, $local=TRUE)
    {
        if ($local) {
            PHPWS_Text::makeRelative($url);
        }
        $this->url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/', '', $this->url);
    }

    function isActive()
    {
        return (bool)$this->active;
    }

    function isHomeKey()
    {
        return ($this->module == 'home' ? TRUE : FALSE);
    }

    function &getHomeKey()
    {
        if (!isset($GLOBALS['Home_Key'])) {
            $GLOBALS['Home_Key'] = & new Key(0);
        }
        return $GLOBALS['Home_Key'];
    }

    function flag()
    {
        $GLOBALS['Current_Flag'] = &$this;
    }

    /**
     * A little kludge code that adds a where clause for 
     * restricted users to your database query
     */
    function addRestrictWhere(&$db)
    {
        $db->addWhere('key_id', 0, NULL, NULL, 1);
        $db->addWhere('key_id', 'phpws_key.id', NULL, 'OR', 1);
        $db->addWhere('phpws_key.restricted', '1', '!=', 'AND', 1);
    }

    function drop($key_id)
    {
        $key = & new Key($key_id);
        return $key->delete();
    }

    function getTplTags()
    {
        $module_names = PHPWS_Core::getModuleNames();

        $tpl['ID']      = $this->id;
        $tpl['MODULE']  = $module_names[$this->module];
        $tpl['ITEM_ID'] = $this->item_id;
        $tpl['TITLE']   = $this->title;
        $tpl['URL']     = $this->getUrl();
        $tpl['SUMMARY'] = $this->summary;
        return $tpl;
    }

    function delete()
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', (int)$this->id);
        $db->addWhere('module', $this->module, '=', 'AND', 1);
        $db->addWhere('item_name', $this->item_name, '=', 'AND', 1);
        $db->addWhere('item_id', $this->item_id, '=', 'AND', 1);
        $db->setGroupConj(1, 'OR');
        return $db->delete();
    }

    function getCurrent()
    {
        if (!isset($GLOBALS['Current_Flag'])) {
            if (isset($_REQUEST['module'])) {
                return NULL;
            } else {
                return Key::getHomeKey();
            }
        } else {
            return $GLOBALS['Current_Flag'];
        }
    }

    function modulesInUse()
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addColumn('module');
        $db->addColumn('modules.proper_name');
        $db->addWhere('module', 'modules.title');
        $db->addOrder('phpws_key.module');
        $db->setIndexBy('module');
        $db->setDistinct(true);
        return $db->select('col');
    }

    function viewed()
    {
        if (!$this->id || $this->isHomeKey()) {
            return;
        }

        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', $this->id);
        return $db->incrementColumn('times_viewed');
    }

}

?>