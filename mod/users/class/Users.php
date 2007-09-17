<?php

/**
 * Class containing all user information
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('users', 'Permission.php');
PHPWS_Core::requireConfig('users');
require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';

if (!defined('ALLOWED_USERNAME_CHARACTERS')) {
    define('ALLOWED_USERNAME_CHARACTERS'. '\w');
}

class PHPWS_User {
    var $id             = 0;
    var $username       = null;
    var $deity          = false;
    var $active         = true;
    // method of authorizing the user
    var $authorize      = 0;
    var $last_logged    = 0;
    var $log_count      = 0;
    var $created        = 0;
    var $updated        = 0;
    // if true, they have been approved to log in
    var $approved       = false;
    var $email          = null;
    var $display_name   = null;

    var $_password      = null;
    var $_groups        = null;
    var $_permission    = null;
    var $_user_group    = null;
    var $_auth_key      = null;
    // Indicates whether this is a logged in user
    var $_logged        = false;
    var $_prev_username = null;
 
    function PHPWS_User($id=0)
    {
        if(!$id){
            $auth = PHPWS_User::getUserSetting('default_authorization');
            $this->setAuthorize($auth);
            return;
        }
        $this->setId($id);
        $result = $this->init();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    function init()
    {
        $db = new PHPWS_DB('users');
        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->id = 0;
            return $result;
        }

        if (!$result) {
            $this->id = 0;
            return false;
        }

        $this->loadUserGroups();
        $this->loadPermissions();
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function login()
    {
        $this->setLogged(true);
        $this->setLastLogged(mktime());
        $this->addLogCount();
        $this->makeAuthKey();
        $this->updateOnly();
        $this->loadUserGroups();
        $this->loadPermissions();
    }

    function isDuplicateDisplayName($display_name, $id=0)
    {
        if (empty($display_name)) {
            return false;
        }
        $DB = new PHPWS_DB('users');
        $DB->addWhere('display_name', $display_name);
        if ($id) {
            $DB->addWhere('id', $id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    function isDuplicateUsername($username, $id=0)
    {
        $DB = new PHPWS_DB('users');
        $DB->addWhere('username', $username);
        if ($id) {
            $DB->addWhere('id', $id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    function isDuplicateGroup($name, $id=0)
    {
        $DB = new PHPWS_DB('users_groups');
        $DB->addWhere('name', $name);
        if ($id) {
            $DB->addWhere('user_id', $id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    function isDuplicateEmail()
    {
        if (empty($this->email))
            return false;

        $DB = new PHPWS_DB('users');
        $DB->addWhere('email', $this->email);
        if ($this->id) {
            $DB->addWhere('id', $this->id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }


    function setUsername($username)
    {
        $username = strtolower($username);
        if (empty($username) || preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', $username)) {
            return PHPWS_Error::get(USER_ERR_BAD_USERNAME, 'users',
                                    'setUsername', $username);
        }
   
        if (strlen($username) < USERNAME_LENGTH) {
            return PHPWS_Error::get(USER_ERR_BAD_USERNAME, 'users',
                                    'setUsername', $username);
        }
   
        if ($this->isDuplicateUsername($username, $this->id) ||
            $this->isDuplicateDisplayName($username, $this->id)) {
            return PHPWS_Error::get(USER_ERR_DUP_USERNAME, 'users',
                                    'setUsername', $username); ;
        }

        if ($this->isDuplicateGroup($username, $this->id)) {
            return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, 'users',
                                    'setUsername', $username); ;
        }

        $this->username = $username;
    
        return true;
    }

    function getUsername(){
        return $this->username;
    }

    function setPassword($password, $hashPass=true){
        if ($hashPass) {
            $this->_password = md5($this->username . $password);
        } else {
            $this->_password = $password;
        }
    }

    function checkPassword($pass1, $pass2){
        if (empty($pass1) || empty($pass2)) {
            return PHPWS_Error::get(USER_ERR_PASSWORD_LENGTH, 'users', 'checkPassword');
        }
        elseif ($pass1 != $pass2) {
            return PHPWS_Error::get(USER_ERR_PASSWORD_MATCH, 'users', 'checkPassword');
        }
        elseif(strlen($pass1) < PASSWORD_LENGTH) {
            return PHPWS_Error::get(USER_ERR_PASSWORD_LENGTH, 'users', 'checkPassword');
        }
        elseif(preg_match('/(' . implode('|', unserialize(BAD_PASSWORDS)) . ')/i', $pass1)) {
            return PHPWS_Error::get(USER_ERR_PASSWORD_EASY, 'users', 'checkPassword');
        }
        else {
            return true;
        }
    }

    function getPassword()
    {
        return $this->_password;
    }

    function setLogged($status)
    {
        $this->_logged = $status;
    }

    function isLogged()
    {
        return (bool)$this->_logged;
    }

    function setLastLogged($time)
    {
        $this->last_logged = $time;
    }

    function getLastLogged($mode=null)
    {
        if (empty($mode))
            return $this->last_logged;
        else {
            if ($this->last_logged == 0 || empty($this->last_logged))
                return null;
            else
                return strftime($mode, $this->last_logged);
        }
    }

    function addLogCount()
    {
        $this->log_count++;
    }

    function getLogCount()
    {
        return $this->log_count;
    }

    function isUser()
    {
        return (bool)$this->id;
    }

    function setDeity($deity)
    {
        $this->deity = (bool)$deity;
    }

    function isDeity()
    {
        return $this->deity;
    }

    function setActive($active)
    {
        $this->active = (bool)$active;
    }

    function isActive()
    {
        return (bool)$this->active;
    }

    function setAuthorize($authorize)
    {
        $this->authorize = (int)$authorize;
    }

    function getAuthorize()
    {
        return $this->authorize;
    }

    function setApproved($approve)
    {
        $this->approved = (bool)$approve;
    }

    function isApproved()
    {
        return (bool)$this->approved;
    }

    function setEmail($email)
    {
        $this->email = $email;

        if (!PHPWS_Text::isValidInput($email, 'email')) {
            return PHPWS_Error::get(USER_ERR_BAD_EMAIL, 'users', 'setEmail');
        }

        if ($this->isDuplicateEmail()) {
            return PHPWS_Error::get(USER_ERR_DUP_EMAIL, 'users', 'setEmail');
        }

        return true;
    }

    function getEmail($html=false, $showAddress=false)
    {
        if ($html == true){
            if ($showAddress == true) {
                return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->email);
            }
            else {
                return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->getDisplayName());
            }
        }
        else {
            return $this->email;
        }
    }

    function setDisplayName($name)
    {
        if (empty($name)) {
            $this->display_name = $this->username;
            return true;
        }

        if (preg_match('/[^\w\s]/', $name)) {
            return PHPWS_Error::get(USER_ERR_BAD_DISPLAY_NAME, 'users',
                                    'setUsername', $name);
        }

        if (strlen($name) < DISPLAY_NAME_LENGTH) {
            return PHPWS_Error::get(USER_ERR_BAD_DISPLAY_NAME, 'users',
                                    'setUsername', $name);
        }

        if ($this->isDuplicateUsername($name, $this->id) || 
            $this->isDuplicateDisplayName($name, $this->id)) {
            return PHPWS_Error::get(USER_ERR_DUP_USERNAME, 'users',
                                    'setDisplayName', $name); ;
        }

        $this->display_name = $name;

        return true;
    }


    function getDisplayName()
    {
        if (empty($this->display_name)) {
            return $this->username;
        } else {
            return $this->display_name;
        }
    }

    function loadUserGroups()
    {
        $group = $this->getUserGroup();

        if (PEAR::isError($group)){
            PHPWS_Error::log($group);
            return false;
        }

        $this->_user_group = $groupList[] = $group;

        $DB = new PHPWS_DB('users_members');
        $DB->addWhere('member_id', $group);
        $DB->addColumn('group_id');
        $result = $DB->select('col');

        if (PEAR::isError($group)){
            PHPWS_Error::log($group);
            return false;
        }
    
        if (is_array($result))
            $groupList = array_merge($result, $groupList);

        $this->setGroups($groupList);
        return true;
    }


    function setGroups($groups)
    {
        $this->_groups = $groups;
    }

    function getGroups()
    {
        return $this->_groups;
    }

    function canChangePassword()
    {
        return ($this->authorize == LOCAL_AUTHORIZATION || $this->authorize == GLOBAL_AUTHORIZATION) ? true : false;
    }

    function verifyAuthKey()
    {
        if (!isset($_REQUEST['authkey']) || $_REQUEST['authkey'] !== $this->getAuthKey())
            return false;

        return true;
    }

    function deityAllow()
    {
        if (!$this->verifyAuthKey() || !$this->isDeity()) {
            return false;
        }
        return true;
    }

    function allowedItem($module, $item_id, $itemname=null)
    {
        return $this->_permission->allowedItem($module, $item_id, $itemname);
    }

    function allow($module, $subpermission=null, $item_id=null, $itemname=null, $verify=false)
    {
        if (!$this->isUser() || !isset($this->_permission)) {
            return false;
        }

        if ($verify && !$this->verifyAuthKey()) {
            return false;
        }

        if ($this->isDeity()) {
            return true;
        }

        PHPWS_Core::initModClass('users', 'Permission.php');
        return $this->_permission->allow($module, $subpermission, $item_id, $itemname);
    }

    /**
     * Crutch function for versions prior to 0.x
     */
    function allow_access($itemName, $subpermission=null, $item_id=null)
    {
        return $this->allow($itemName, $subpermission, $item_id);
    }


    function save()
    {
        PHPWS_Core::initModClass('users', 'Group.php');

        if (!$this->id) {
            $newUser = true;
        }
        else {
            $newUser = false;
        }


        $result = ($this->isDuplicateUsername($this->username, $this->id) ||
                   $this->isDuplicateDisplayName($this->username, $this->id) ||
                   $this->isDuplicateUsername($this->display_name, $this->id) ||
                   $this->isDuplicateDisplayName($this->display_name, $this->id)) ? true : false;
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($result == true) {
            return PHPWS_Error::get(USER_ERR_DUP_USERNAME, 'users', 'save');
        }

        $result = $this->isDuplicateEmail();
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($result == true) {
            return PHPWS_Error::get(USER_ERR_DUP_EMAIL, 'users', 'save');
        }

        $result = $this->isDuplicateGroup($this->username, $this->id);
        if (PEAR::isError($result)) {
            return $result;
        }
            
        if ($result == true) {
            return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, 'users', 'save');
        }

        if (empty($this->display_name)) {
            $this->display_name = $this->username;
        }

        if (!isset($this->authorize)) {
            $this->authorize = $this->getUserSetting('default_authorization');
        }

        if ($newUser == true) {
            $this->created = mktime();
        }
        else {
            $this->updated = mktime();
        }

        $db = new PHPWS_DB('users');
        $result = $db->saveObject($this);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
        }

        
        if ($this->authorize > 0) {
            if ($this->authorize == LOCAL_AUTHORIZATION) {
                $result = $this->saveLocalAuthorization();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                }
            }
            elseif ($this->authorize == GLOBAL_AUTHORIZATION) {
                $result = $this->saveGlobalAuthorization();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                }
            }
        }


        if ($newUser) {
            return $this->createGroup();
        }
        else {
            return $this->updateGroup();
        }
    }

    function updateOnly()
    {
        $db = new PHPWS_DB('users');
        $result = $db->saveObject($this);
        
        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
        }

        return $result;
    }

    function makeAuthKey()
    {
        $key = rand();
        $this->_auth_key = md5($this->username . $key . mktime());
    }

    function getAuthKey()
    {
        return $this->_auth_key;
    }

    function saveLocalAuthorization()
    {
        if (empty($this->username) || empty($this->_password)) {
            return false;
        }

        $db = new PHPWS_DB('user_authorization');
        if (!empty($this->_prev_username)) {
            $db->addWhere('username', $this->_prev_username);
        } else {
            $db->addWhere('username', $this->username);
        }
        $result = $db->delete();
        $db->resetWhere();

        $db->addValue('username', $this->username);
        $db->addValue('password', $this->_password);
        return $db->insert();
    }

    function saveGlobalAuthorization()
    {

    }

    function createGroup()
    {
        $group = new PHPWS_Group;
        $group->setName($this->getUsername());
        $group->setUserId($this->getId());
        $group->setActive($this->isActive());
        $result = $group->save();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            $this->kill();
            return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
        } else {
            return true;
        }
    }

    function updateGroup()
    {
        $db = new PHPWS_DB('users_groups');
        $db->addWhere('user_id', $this->id);
        $db->addColumn('id');
        $result = $db->select('one');

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return PHPWS_Error::get(USER_ERROR, 'users', 'updateGroup');
        }

        $group = new PHPWS_Group($result);

        $group->setName($this->getUsername());
        $group->setActive($this->isActive());

        $result = $group->save();
        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            $this->kill();
            return PHPWS_Error::get(USER_ERROR, 'users', 'updateGroup');
        } else {
            return true;
        }
    }


    function getUserGroup()
    {
        if (isset($this->_user_group)) {
            return $this->_user_group;
        }

        $db = new PHPWS_DB('users_groups');
        $db->addWhere('user_id', $this->getId());
        $db->addColumn('id');

        $result = $db->select('one');

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!isset($result)) {
            return PHPWS_Error::get(USER_ERR_MISSING_GROUP, 'users', 'getUserGroup');
        } else {
            return $result;
        }
    }

    function disallow($message=null)
    {
        if (!isset($message)){
            $message = dgettext('users', 'Improper permission level for action requested.');
        }
        Security::log($message);
        PHPWS_Core::errorPage('403');
    }


    function getSettings()
    {
        $DB = new PHPWS_DB('users_config');
        return $DB->select('row');
    }

    function resetUserSettings()
    {
        unset($GLOBALS['User_Settings']);
    }

    function getUserSetting($setting, $refresh=false)
    {
        return PHPWS_Settings::get('users', $setting);
    }

    function loadPermissions($loadAll=true)
    {
        if ($loadAll == true){
            $groups = $this->getGroups();
        } else {
            $groups[] = $this->getUserGroup();
        }

        $this->_permission = new Users_Permission($groups);
    }

    function kill()
    {
        if (!$this->id) {
            return false;
        }

        $db = new PHPWS_DB('users');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
    
        if ($this->authorize == LOCAL_AUTHORIZATION) {
            $db2 = new PHPWS_DB('user_authorization');
            $db2->addWhere('username', $this->username);
            $result = $db2->delete();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        
        $user_group = new PHPWS_Group($this->getUserGroup());
        $user_group->kill();
    
    }

    function savePermissions($key)
    {
        if (!is_object($key) || strtolower(get_class($key) == 'key')) {
            return false;
        }

        if (!PHPWS_Core::moduleExists($key->module)) {
            return PHPWS_Error::get(PHPWS_NO_MOD_FOUND, 'core', __CLASS__ . '::' . __FUNCTION__);
        }
      
        PHPWS_Core::initModClass('users', 'Permission.php');
        return Users_Permission::savePermissions($key);
    }

    function getAllGroups()
    {
        PHPWS_Core::initModClass('users', 'Action.php');
        return User_Action::getGroups('group');
    }

    function getUnrestrictedLevels()
    {
        if (!isset($this->_permission)) {
            $this->loadPermissions();
        }

        if (!empty($this->_permission->levels)) {
            return array_keys($this->_permission->levels, UNRESTRICTED_PERMISSION);
        }
    }


    function getPermissionLevel($module)
    {
        if ($this->isDeity()) {
            return UNRESTRICTED_PERMISSION;
        }

        PHPWS_Core::initModClass('users', 'Permission.php');

        if (!isset($this->_permission)) {
            $this->loadPermissions();
        }

        return $this->_permission->getPermissionLevel($module);
    }

    function getUserTpl()
    { 
        // Don't let a deity change their deity status
        // Don't let non-deities change status

        if (Current_User::isDeity() && !Current_User::isUser($this->id)) {
            if ($this->isDeity()) {
                $dvars['QUESTION'] = dgettext('users', 'Are you sure you want to remove deity status?');
                $dvars['ADDRESS']  = PHPWS_Text::linkAddress('users', array('action'=>'admin', 'command'=>'mortalize_user', 'user_id'=>$this->id), 1);
                $dvars['LINK']     = dgettext('users', 'Deity');
                $links[] = javascript('confirm', $dvars);
            } else {
                $dvars['QUESTION'] = dgettext('users', 'Are you sure you want to deify this user?');
                $dvars['ADDRESS']  = PHPWS_Text::linkAddress('users', array('action'=>'admin', 'command'=>'deify_user', 'user_id'=>$this->id), 1);
                $dvars['LINK']     = dgettext('users', 'Mortal');
                $links[] = javascript('confirm', $dvars);
            }
        }

        if ($this->isActive()) {
            $linkVar['command'] = 'deactivateUser';
            $links[] = PHPWS_Text::secureLink(dgettext('users', 'Deactivate'), 'users', $linkVar);

            $template['ACTIVE'] =  dgettext('users', 'Yes');
        } else {
            $linkVar['command'] = 'activateUser';
            $links[] = PHPWS_Text::secureLink(dgettext('users', 'Activate'), 'users', $linkVar);

            $template['ACTIVE'] = dgettext('users', 'No');
        }

        $logged = $this->getLastLogged('%c');

        if (empty($logged)) {
            $template['LAST_LOGGED'] =  dgettext('users', 'Never');
        } else {
            $template['LAST_LOGGED'] = $logged;
        }

        $template['EMAIL'] = $this->getEmail(true, true);
    
        $linkVar['action'] = 'admin';
        $linkVar['user_id'] = $this->id;

        $jsvar['QUESTION'] = sprintf(dgettext('users', 'Are you certain you want to delete the user &quot;%s&quot; permanently?'),
                                     $this->getUsername());
        $jsvar['ADDRESS']  = 'index.php?module=users&amp;action=admin&amp;command=deleteUser&amp;user_id='
            . $this->id . '&amp;authkey=' . Current_User::getAuthKey();
        $jsvar['LINK']     = dgettext('users', 'Delete');
    
    
        $linkVar['command'] = 'editUser';
        $links[] = PHPWS_Text::secureLink(dgettext('users', 'Edit'), 'users', $linkVar);

        $linkVar['command'] = 'setUserPermissions';
        $links[] = PHPWS_Text::secureLink(dgettext('users', 'Permissions'), 'users', $linkVar);

        if (!$this->isDeity() && ($this->id != Current_User::getId())) {
            $links[] = Layout::getJavascript('confirm', $jsvar);
        }

        $template['ACTIONS'] = implode(' | ', $links);

        if ($this->deity && !Current_User::isDeity()) {
            unset($template['ACTIONS']);
        }

        return $template;
    }


    function registerPermissions($module)
    {
        return Users_Permission::registerPermissions($module);
    }
}

?>
