<?php

/**
 * Small class to help modules plug in features from other modules
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define('KEY_NOT_RESTRICTED',    0);
define('KEY_LOGGED_RESTRICTED', 1);
define('KEY_GROUP_RESTRICTED',  2);

if (!isset($_REQUEST['module'])) {
    $GLOBALS['PHPWS_Key'] = Key::getHomeKey();
} else {
    $GLOBALS['PHPWS_Key'] = null;
}

class Key {
    // if the id is 0 (zero) then this is a _dummy_ key
    // dummy keys are not saved
    var $id              = null;
    var $module          = null;
    var $item_name       = null;
    var $item_id         = null;
    var $title           = null;
    var $summary         = null;
    var $url             = null;
    var $active          = 1;

    // if KEY_LOGGED_RESTRICTED, then only logged in users will access
    // if KEY_GROUP_RESTRICTED, user must be in group list
    var $restricted      = 0;

    var $create_date     = 0;
    var $update_date     = 0;

    var $creator         = null;
    var $creator_id      = 0;

    var $updater         = null;
    var $updater_id      = 0;

    // contains permission allow name for editing
    var $edit_permission = null;

    var $times_viewed    = 0;


    // groups allowed to view
    var $_view_groups    = null;
    // groups allowed to edit
    var $_edit_groups    = null;

    var $_error          = null;
  
    function Key($id=null)
    {
        if (empty($id)) {
            return null;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function isKey($key)
    {
        if (is_object($key) && strtolower(get_class($key)) == 'key') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns the url in a link
     */
    function getUrl($full_path=false)
    {
        if ($full_path) {
            return sprintf('<a href="%s%s">%s</a>', PHPWS_Core::getHomeHttp(), $this->url, $this->title);
        } else {
            return sprintf('<a href="%s">%s</a>', $this->url, $this->title);
        }
    }

    function getCreateDate($format = '%c')
    {
        return strftime($format, PHPWS_Time::getServerTime($this->create_date));
    }

    function getUpdateDate($format = '%c')
    {
        return strftime($format, PHPWS_Time::getServerTime($this->update_date));
    }

    function restrictToLogged()
    {
        $this->restricted = KEY_LOGGED_RESTRICTED;
    }

    function restrictToGroups($groups=null)
    {
        if (!is_array($groups)) {
            return false;
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
            $this->edit_permission = null;
        } else {
            $this->edit_permission = strip_tags($permission);
        }
    }

    function loadViewGroups()
    {
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

    function getViewGroups()
    {
        if (!isset($this->_view_groups)) {
            $this->loadViewGroups();
        }
        return $this->_view_groups;
    }

    function loadEditGroups()
    {
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

    function getEditGroups()
    {
        if (!isset($this->_edit_groups)) {
            $this->loadEditGroups();
        }
        return $this->_edit_groups;
    }


    function allowView()
    {
        if (Current_User::allow($this->module, $this->edit_permission,
                                      $this->item_id, $this->item_name)) {
            return true;
        } elseif (!$this->active) {
            return false;
        }

        if (!$this->restricted) {
            return true;
        } else {
            if ($this->restricted == KEY_LOGGED_RESTRICTED) {
                return Current_User::isLogged();
            } elseif ($this->restricted == KEY_GROUP_RESTRICTED) {
                if (Current_User::allow($this->module)) {
                    return true;
                } else {
                    $user_groups = Current_User::getGroups();
                    if (empty($user_groups)) {
                        return false;
                    } else {
                        return (bool)array_intersect($user_groups, $this->getViewGroups());
                    }
                }
            }
        }

        return true;
    }

    function allowEdit()
    {
        if (empty($this->edit_permission)) {
            return true;
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
            $this->id = null;
        }

        return $result;
    }

    function save()
    {
        // No need to save dummy keys
        if ($this->id === 0) {
            return true;
        }

        if (empty($this->module) || empty($this->item_id)) {
            return false;
        }
        
        if (empty($this->item_name) || $this->item_name == 'home') {
            $this->item_name = $this->module;
        }

        if (empty($this->create_date)) {
            $this->create_date = mktime();
        }

        if (empty($this->creator)) {
            $this->creator = Current_User::getDisplayName();
            $this->creator_id = Current_User::getId();
        }

        $this->updater = Current_User::getDisplayName();
        $this->updater_id = Current_User::getId();

        $this->update_date = mktime();

        $db = new PHPWS_DB('phpws_key');

        if (!$this->id) {
            $db->addWhere('module', $this->module);
            $db->addWhere('item_name', $this->item_name);
            $db->addWhere('item_id', $this->item_id);
            $result = $db->select('row');
            if (PEAR::isError($result)) {
                return $result;
            } elseif ($result) {
                return PHPWS_Error::get(KEY_DUPLICATE, 'core', 'Key::save', 
                                        sprintf('%s-%s-%s', $this->module, $this->itemname, $this->item_id));
            }
            $db->reset();
        }

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }

        $view_db = new PHPWS_DB('phpws_key_view');
        $view_db->addWhere('key_id', $this->id);
        $result = $view_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $edit_db = new PHPWS_DB('phpws_key_edit');
        $edit_db->addWhere('key_id', $this->id);
        $result = $edit_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        // we don't care if restricted is 0 because everyone can view
        // we don't care if it is KEY_LOGGED_RESTRICTED either because
        // just checking log status covers it

        if ($this->restricted == KEY_GROUP_RESTRICTED) {
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

        return true;
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
        $this->title = PHPWS_Text::condense($title);
    }

    function setSummary($summary)
    {
        $summary = preg_replace('/(<|&lt;|\[).*(>|&gt;|\])/sUi', ' ', $summary);
        $this->summary = PHPWS_Text::condense($summary, 255);
    }

    function setUrl($url, $local=true)
    {
        if (preg_match('/^<a/', trim($url))) {
            $url = preg_replace('/<a href="(.*)".*<\/a>/iU', '\\1', $url);
        }

        if ($local) {
            PHPWS_Text::makeRelative($url, false);
        }
        $this->url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/', '', $this->url);
    }

    function isActive()
    {
        return (bool)$this->active;
    }

    /**
     * Returns true, if the key is from the home page
     */
    function isHomeKey()
    {
        return ($this->module == 'home' ? true : false);
    }

    function &getHomeKey()
    {
        if (!isset($GLOBALS['Home_Key'])) {
            $key = & new Key;
            $key->id = 0;
            $key->module = $key->item_name = 'home';
            $key->item_id = 0;
            $key->setTitle(_('Home'));
            $key->setUrl('index.php');

            $GLOBALS['Home_Key'] = $key;
        }
        return $GLOBALS['Home_Key'];
    }

    function flag()
    {
        if (!isset($this->id)) {
            $this->id = 0;
        }
        $this->viewed();
        $GLOBALS['Current_Flag'] = &$this;
    }

    function drop($key_id)
    {
        $key = & new Key($key_id);
        return $key->delete();
    }

    function getTplTags()
    {
        $module_names = PHPWS_Core::getModuleNames();

        $tpl['ID']          = $this->id;
        $tpl['MODULE']      = $module_names[$this->module];
        $tpl['ITEM_ID']     = $this->item_id;
        $tpl['TITLE']       = $this->title;
        $tpl['URL']         = $this->getUrl();
        $tpl['SUMMARY']     = $this->summary;
        $tpl['CREATOR']     = $this->creator;
        $tpl['UPDATER']     = $this->updater;
        $tpl['CREATE_DATE'] = $this->getCreateDate();
        $tpl['UPDATE_DATE'] = $this->getUpdateDate();
        return $tpl;
    }

    function delete()
    {
        $all_is_well = true;
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        $this->unregister();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = false;
        }

        $db->reset();
        $db->setTable('phpws_key_edit');
        $db->addWhere('key_id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = false;
        }
        
        $db->reset();
        $db->setTable('phpws_key_view');
        $db->addWhere('key_id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = false;
        }
        return $all_is_well;
    }

    /**
     * Retrieves the current flagged key. Will return the home key if
     * on the home page and allow_home is true.
     */
    function getCurrent($allow_home=true)
    {
        if (!isset($GLOBALS['Current_Flag'])) {
            if (isset($_REQUEST['module']) || !$allow_home) {
                return null;
            } else {
                return Key::getHomeKey();
            }
        } else {
            return $GLOBALS['Current_Flag'];
        }
    }


    /**
     * added limitations to a select query to only pull rows that
     * the user is allowed to see. This function does does not works alone.
     * it requires a database object to already be started.
     *
     * The user module MUST be active for this function to work.
     * This Key function cannot be called without it.
     *
     * If the user is a deity or an unrestricted user, no change will be made
     * to your db object.
     *
     */
    function restrictView(&$db, $module=null)
    {
        $source_table = $db->tables[0];

        if ($source_table == 'phpws_key') {
            if (!isset($db->tables[1])) {
                return PHPWS_Error::get(KEY_RESTRICT_NO_TABLE, 'core', 'Key::restrictView');
            }
            $source_table = $db->tables[1];
            $key_table = true;
        } else {
            $key_table = false;
        }

        if (!$key_table) {
            $db->addWhere('key_id', 0, null, null, 'empty_key');
        } else {
            $db->addWhere($source_table . '.key_id', '0', null, null, 'empty_key');
        }

        $db->addWhere('phpws_key.active', 1, null, null, 'active');

        $db->groupIn('empty_key', 'active');
        $db->setGroupConj('active', 'or');

        if (!$key_table) {
            $db->addJoin('left', $source_table, 'phpws_key', 'key_id', 'id');
        } else {
            $db->addJoin('left', 'phpws_key', $source_table, 'id', 'key_id');
        }


        if ( Current_User::isDeity() || 
             (isset($module) && Current_User::isUnrestricted($module) )
             ) {
            return;
        } 


        if (!Current_User::isLogged()) {
            $db->addWhere('phpws_key.restricted', 0, null, 'and', 'active');
            return;
        } else {
            $groups = Current_User::getGroups();
            if (empty($groups)) {
                return;
            }

            $db->addJoin('left', 'phpws_key', 'phpws_key_view', 'id', 'key_id');
            
            // if key only has a level 1 restriction, a logged user can view it
            $db->addWhere('phpws_key.restricted', 1, '<=', null, 'restrict_1');

            // at level 2, the user must be in a group given view permissions
            $db->addWhere('phpws_key.restricted', 2, '=', null, 'restrict_2');

            $db->addWhere('phpws_key_view.group_id', $groups, 'in', null, 'restrict_2');

            if (empty($module)) {
                $levels = Current_User::getUnrestrictedLevels();
                if (!empty($levels)) {
                    $db->addWhere('phpws_key.module', $levels, null, null, 'permission');
                    $db->groupIn('active', 'permission');
                    $db->groupIn('permission', 'restrict_1');
                    $db->setGroupConj('restrict_1', 'or');
                } else {
                    $db->groupIn('active', 'restrict_1');
                }
            } else {
                $db->groupIn('active', 'restrict_1');
            }


            $db->setGroupConj('restrict_2', 'or');
            $db->setGroupConj('active', 'or');
            $db->groupIn('restrict_1', 'restrict_2');

            return;
        }
    }


    /**
     * Adds limits to a db select query to only pull items the user 
     * has permissions to view
     * 
     * Note that BEFORE this is called, the developer should check whether
     * the user has ANY rights to edit items in the first place.
     * In other words, if Current_User::allow('module', 'edit_permission') == false
     * then they shouldn't even use this function. If it is used anyway, a forced negative
     * will be added (i.e. where 1 = 0);
     */

    function restrictEdit(&$db, $module, $edit_permission)
    {
        if (Current_User::isDeity()) {
            return;
        }

        // if the user doesn't have rights for the module or subpermissions,
        // then we just stymie the whole query
        if (!Current_User::allow($module, $edit_permission)) {
            $db->setQWhere('1=0');
            return;
        }

        // If the current user has unrestricted rights to edit the item
        // linked to this key, no further restrictions are necessary
        if ( Current_User::isUnrestricted($module) ) {
            return;
        } else {
            $db->setDistinct(1);
            $source_table = $db->tables[0];

            $groups = Current_User::getGroups();
            // if the current user is not in any groups (unlikely)
            // then fail
            if (empty($groups)) {
                $db->setQWhere('1=0');
                return;
            }

            $db->addJoin('left', $source_table, 'phpws_key', 'key_id', 'id');
            $db->addWhere('key_id', 0);
            $db->addWhere('phpws_key.id', 'phpws_key_edit.key_id', null, null, 'key_1');
            $db->addWhere('phpws_key_edit.group_id', $groups, 'in', null, 'key_1');
            $db->setGroupConj('key_1', 'or');
            return;
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

        if ( isset($_SESSION['Key_Views']) &&
             in_array($this->id, $_SESSION['Key_Views']) ) {
            return;
        }

        $_SESSION['Key_Views'][] = $this->id;
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', $this->id);
        return $db->incrementColumn('times_viewed');
    }

    /**
     * A set of checks on a key to see if it is usable for content indexing
     */
    function checkKey(&$key, $allow_home_key=true) {
        if ( empty($key) || isset($key->_error) ) {
            return false;
        }

        if (!$allow_home_key) {
            if ($key->isHomeKey()) {
                return false;
            }

            if (!$key->id) {
                return false;
            }
        }

        return true;
    }

    function isDummy($allow_home=false)
    {
        if ($this->id === 0) {
            if ($this->isHomeKey() && $allow_home) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function blockPopup($module)
    {
        $GLOBALS['Key_Blocked_Popups'][] = $module;
    }

    function isBlocked($module)
    {
        if (empty($module) ||
            !is_string($module) ||
            !isset($GLOBALS['Key_Blocked_Popups']) ||
            !is_array($GLOBALS['Key_Blocked_Popups'])) {
            return false;
        }

        return in_array($module, $GLOBALS['Key_Blocked_Popups']);
    }

    function unregister()
    {
        $success = true;
        $db = & new PHPWS_DB('phpws_key_register');
        $db->addColumn('module');
        $result = $db->select('col');
        if (empty($result)) {
            return true;
        }

        foreach ($result as $module) {
            $filename = sprintf('%smod/%s/inc/key.php', PHPWS_SOURCE_DIR, $module);

            if (!is_file($filename)) {
                PHPWS_Error::log(KEY_UNREG_FILE_MISSING, 'core', 'PHPWS_Key::unregister', $filename);
                continue;
            }

            require_once $filename;

            $func_name = $module . '_unregister_key';
            if (!function_exists($func_name)) {
                PHPWS_Error::log(KEY_UNREG_FUNC_MISSING, 'core', 'PHPWS_Key::unregister', $func_name);
                continue;
            }

            $result = $func_name($this);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $success = false;
            }
        }
        return $success;
    }

    function registerModule($module)
    {
        $db = & new PHPWS_DB('phpws_key_register');
        $db->addValue('module', $module);
        return $db->insert();
    }

    /**
     * Unregisters a module by pulling current keys and
     * deleting them individually. Although this takes longer
     * than simply wiping the table, it cleans up old associations. Function
     * also removes the module from the phpws_key_register table.
     * Returns true is all is well, false if there was a problem cleaning
     * up the associations, and an error object if a major problem occurred.
     */
    function unregisterModule($module)
    {
        $error_free = true;

        $db1 = & new PHPWS_DB('phpws_key');
        $db1->addWhere('module', $module);
        $result = $db1->getObjects('Key');
        if (PEAR::isError($result)) {
            return $result;
        }

        if (!empty($result)) {
            foreach ($result as $key) {
                $result = $key->delete();
                if (!$result) {
                    $error_free = false;
                }
            }
        }

        $db2 = & new PHPWS_DB('phpws_key_register');
        $db2->addWhere('module', $module);
        $result = $db2->delete();
        if (PEAR::isError($result)) {
            return $result;
        }

        return $error_free;
    }

    function getAllIds($module)
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addColumn('id');
        $db->addWhere('module', $module);
        return $db->select('col');
    }

}

?>