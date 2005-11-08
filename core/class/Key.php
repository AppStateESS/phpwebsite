<?php

/**
 * Small class to help modules plug in features from other modules
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define('KEY_LOGGED_RESTRICTED', 1);
define('KEY_GROUP_RESTRICTED', 2);

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

    // if KEY_LOGGED_RESTRICTED, then only logged in users will access
    // if KEY_GROUP_RESTRICTED, user must be in group list
    var $restricted      = 0;

    var $create_date     = 0;
    var $update_date     = 0;

    // contains permission allow name for editing
    var $edit_permission = NULL;

    var $times_viewed    = 0;

    // groups allowed to view
    var $_view_groups    = NULL;
    var $_edit_groups    = NULL;
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

    function restrictToLogged()
    {
        $this->restricted = KEY_LOGGED_RESTRICTED;
    }

    function restrictToGroups($groups=NULL)
    {
        if (!is_array($groups)) {
            return FALSE;
        }
        $this->restricted = KEY_GROUP_RESTRICTED;
        if (!empty($groups)) {
            return $this->setViewGroups($groups);
        }
    }

    function setViewGroups($groups)
    {
        foreach ($groups as $group_id) {
            if (is_numeric($group_id)) {
                $this->_view_groups[] = (int)$group_id;
            }
        }
    }

    // restricted means that only logged users can access
    function isRestricted()
    {
        return (bool)$this->restricted;
    }

    function setEditPermission($permission)
    {
        if (empty($permission)) {
            $this->edit_permission = NULL;
        } else {
            $this->edit_permission = strip_tags($permission);
        }
    }

    function getViewGroups()
    {
        if (!isset($this->_view_groups)) {
            $db = & new PHPWS_DB('phpws_key_view');
            $db->addWhere('key_id', $this->id);
            $db->addColumn('group_id');
            $result = $db->select('col');

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return array();
            }
            $this->_view_groups = $result;
        }
        return $this->_view_groups;
    }

    function getEditGroups()
    {
        if (!isset($this->_edit_groups)) {
            $db = & new PHPWS_DB('phpws_key_edit');
            $db->addWhere('key_id', $this->id);
            $db->addColumn('group_id');
            $result = $db->select('col');
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return array();
            }
            $this->_edit_groups = $result;
        }
        return $this->_edit_groups;
    }


    function allowView()
    {
        if (!$this->restricted) {
            return TRUE;
        } else {
            if ($this->restricted == KEY_LOGGED_RESTRICTED) {
                return Current_User::isLogged();
            } elseif ($this->restricted == KEY_GROUP_RESTRICTED) {
                if (Current_User::allow($this->module)) {
                    return TRUE;
                } else {
                    $user_groups = Current_User::getGroups();
                    if (empty($user_groups)) {
                        return false;
                    } else {
                        return in_array($user_groups, $this->getViewGroups());
                    }
                }
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

        $view_db = & new PHPWS_DB('phpws_key_view');
        $view_db->addWhere('key_id', $this->id);
        $result = $view_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }

        $edit_db = & new PHPWS_DB('phpws_key_edit');
        $edit_db->addWhere('key_id', $this->id);
        $result = $edit_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        // we don't care if restricted is 0 because everyone can view
        // we don't care if it is 1 either because just checking log
        // status covers it

        if ($this->restricted == 2) {
            if (!empty($this->_view_groups) && is_array($this->_view_groups)) {
                $view_db->reset();

                $this->_view_groups = array_unique($this->_view_groups);

                foreach ($this->_view_groups as $group_id) {
                    $view_db->resetValues();
                    $view_db->addValue('key_id', $this->id);
                    $view_db->addValue('group_id', $group_id);
                    $view_db->insert();
                }
            }
        }

        if (!empty($this->_edit_groups) && is_array($this->_edit_groups)) {
            $edit_db->reset();
            $this->_edit_groups = array_unique($this->_edit_groups);
            foreach ($this->_edit_groups as $group_id) {
                $edit_db->resetValues();
                $edit_db->addValue('key_id', $this->id);
                $edit_db->addValue('group_id', $group_id);
                $edit_db->insert();
            }
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
        $all_is_well = TRUE;
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = FALSE;
        }

        $db->reset();
        $db->setTable('phpws_key_edit');
        $db->addWhere('key_id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = FALSE;
        }
        
        $db->reset();
        $db->setTable('phpws_key_view');
        $db->addWhere('key_id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = FALSE;
        }
        return $all_is_well;
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

    function restrictView(&$db, $module)
    {
        if (Current_User::isDeity()) {
            return;
        }

        if (!Current_User::isLogged()) {
            $db->addWhere('phpws_key.restricted', 0);
            $db->addWhere('key_id', 'phpws_key.id');
            return;
        } elseif (Current_User::isUnrestricted($module)) {
            return;
        } else {
            $db->setDistinct(1);
            $groups = Current_User::getGroups();
            if (empty($groups)) {
                return;
            }

            $db->addWhere('key_id', 'phpws_key.id');

            $db->addWhere('phpws_key.restricted', 1, '<=', NULL, 'key_group');
            $db->addWhere('phpws_key.restricted', 2, '=', 'or', 'key_group');
            $db->addWhere('phpws_key.id', 'phpws_key_view.key_id', '=', 'AND', 'key_group');
            $db->addWhere('phpws_key_view.group_id', $groups, 'in', 'AND', 'key_group');

            $db->setGroupConj('key_group', 'and');
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

    function checkKey(&$key, $allow_home_key=TRUE) {
        if ( empty($key) || isset($key->_error) ) {
            return FALSE;
        }

        if (!$allow_home_key) {
            if ($key->isHomeKey()) {
                return FALSE;
            }

            if (!$key->id) {
                return FALSE;
            }
        }

        return TRUE;
    }
}

?>