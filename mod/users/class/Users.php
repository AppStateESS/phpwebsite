<?php

/**
 * Class containing all user information
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('users', 'Permission.php');
PHPWS_Core::configRequireOnce('users', 'config.php');

if (!defined('ALLOWED_USERNAME_CHARACTERS')) {
    define('ALLOWED_USERNAME_CHARACTERS'. '\w');
}


class PHPWS_User {
    var $id            = NULL;
    var $username      = NULL;
    var $deity         = FALSE;
    var $active        = TRUE;
    var $authorize     = NULL;
    var $last_logged   = 0;
    var $log_count     = 0;
    var $created       = 0;
    var $updated       = 0;
    var $approved      = FALSE;
    var $email         = NULL;
    var $display_name  = NULL;

    var $_password     = NULL;
    var $_groups       = NULL;
    var $_permission   = NULL;
    var $_user_group   = NULL;
    var $_auth_key     = NULL;
    // Indicates whether this is a logged in user
    var $_logged       = FALSE;
 
    function PHPWS_User($id=NULL)
    {
        if(!isset($id)){
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
        $db = & new PHPWS_DB('users');
        $result = $db->loadObject($this);

        if (PEAR::isError($result))
            return $result;

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
        $this->setLogged(TRUE);
        $this->setLastLogged(mktime());
        $this->addLogCount();
        $this->makeAuthKey();
        $this->save();
        $this->init();
    }

    function isDuplicateDisplayName($display_name, $id=NULL)
    {
        $DB = & new PHPWS_DB('users');
        $DB->addWhere('display_name', $display_name);
        if (!empty($id)) {
            $DB->addWhere('id', $id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function isDuplicateUsername($username, $id=NULL)
    {
        $DB = & new PHPWS_DB('users');
        $DB->addWhere('username', $username);
        if (!empty($id)) {
            $DB->addWhere('id', $id, '!=');
        }

        $result = $DB->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function isDuplicateGroup($name, $id=NULL)
    {
        $DB = & new PHPWS_DB('users_groups');
        $DB->addWhere('name', $name);
        if (isset($id))
            $DB->addWhere('user_id', $id, '!=');

        $result = $DB->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function isDuplicateEmail()
    {
        if (empty($this->email))
            return FALSE;

        $DB = & new PHPWS_DB('users');
        $DB->addWhere('email', $this->email);
        if (isset($this->id))
            $DB->addWhere('id', $this->id, '!=');

        $result = $DB->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }


    function setUsername($username)
    {
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
    
        return TRUE;
    }

    function getUsername(){
        return $this->username;
    }

    function setPassword($password, $hashPass=TRUE){
        if ($hashPass)
            $this->_password = md5($password);
        else
            $this->_password = $password;
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
            return TRUE;
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

    function getLastLogged($mode=NULL)
    {
        if (empty($mode))
            return $this->last_logged;
        else {
            if ($this->last_logged == 0 || empty($this->last_logged))
                return NULL;
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
        return isset($this->id);
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

        return TRUE;
    }

    function getEmail($html=FALSE, $showAddress=FALSE)
    {
        if ($html == TRUE){
            if ($showAddress == TRUE) {
                return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->email);
            }
            else {
                return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->getDisplayName());
            }
        }
        else
            return $this->email;
    }

    function setDisplayName($name)
    {
        if (empty($name) || preg_match('/[^\w\s]/', $name)) {
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

        return TRUE;
    }


    function getDisplayName()
    {
        if (empty($this->display_name))
            return $this->username;
        else
            return $this->display_name;
    }

    function loadUserGroups()
    {
        $group = $this->getUserGroup();
        if (PEAR::isError($group)){
            echo $group->getMessage();
            return;
        }

        $this->_user_group = $groupList[] = $group;

        $DB = & new PHPWS_DB('users_members');
        $DB->addWhere('member_id', $group);
        $DB->addColumn('group_id');
        $result = $DB->select('col');

        if (PEAR::isError($group)){
            echo $group->getMessage();
            return;
        }
    
        if (is_array($result))
            $groupList = array_merge($result, $groupList);

        $this->setGroups($groupList);
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
        return ($this->authorize == LOCAL_AUTHORIZATION || $this->authorize == GLOBAL_AUTHORIZATION) ? TRUE : FALSE;
    }

    function verifyAuthKey()
    {
        if (!isset($_REQUEST['authkey']) || $_REQUEST['authkey'] !== $this->getAuthKey())
            return FALSE;

        return TRUE;
    }

    function deityAllow()
    {
        if (!$this->verifyAuthKey() || !$this->isDeity()) {
            return FALSE;
        }
        return TRUE;
    }

    function allowedItem($module, $item_id, $itemname=NULL)
    {
        return $this->_permission->allowedItem($module, $item_id, $itemname);
    }

    function allow($module, $subpermission=NULL, $item_id=NULL, $itemname=NULL, $verify=FALSE)
    {
        if (!$this->isLogged()) {
            return FALSE;
        }
        if ($verify && !$this->verifyAuthKey())
            return FALSE;

        PHPWS_Core::initModClass('users', 'Permission.php');
        if ($this->isDeity())
            return TRUE;

        return $this->_permission->allow($module, $subpermission, $item_id, $itemname);
    }

    /**
     * Crutch function for versions prior to 0.x
     */
    function allow_access($itemName, $subpermission=NULL, $item_id=NULL)
    {
        return $this->allow($itemName, $subpermission, $item_id);
    }

    function save()
    {
        PHPWS_Core::initModClass('users', 'Group.php');

        if (!isset($this->id))
            $newUser = TRUE;
        else
            $newUser = FALSE;

        $result = ($this->isDuplicateUsername($this->username, $this->id) ||
                   $this->isDuplicateDisplayName($this->username, $this->id) ||
                   $this->isDuplicateUsername($this->display_name, $this->id) ||
                   $this->isDuplicateDisplayName($this->display_name, $this->id)) ? TRUE : FALSE;
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($result == TRUE) {
            return PHPWS_Error::get(USER_ERR_DUP_USERNAME, 'users', 'save');
        }

        $result = $this->isDuplicateEmail();
        if (PEAR::isError($result))
            return $result;

        if ($result == TRUE)
            return PHPWS_Error::get(USER_ERR_DUP_EMAIL, 'users', 'save');

        $result = $this->isDuplicateGroup($this->username, $this->id);
        if (PEAR::isError($result))
            return $result;

        if ($result == TRUE)
            return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, 'users', 'save');

        if (empty($this->display_name)) {
            $this->display_name = $this->username;
        }

        if (!isset($this->authorize))
            $this->authorize = $this->getUserSetting('default_authorization');

        if ($newUser == TRUE)
            $this->created = mktime();
        else
            $this->updated = mktime();

        $db = & new PHPWS_DB('users');
        $result = $db->saveObject($this);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
        }

        if ($this->authorize > 0 && !$this->isLogged()){
            if ($this->authorize == LOCAL_AUTHORIZATION) {
                $this->saveLocalAuthorization();
            }
            elseif ($this->authorize == GLOBAL_AUTHORIZATION) {
                $this->saveGlobalAuthorization();
            }
        }

        if ($newUser)
            return $this->createGroup();
        else
            return $this->updateGroup();
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
        if (empty($this->username) || empty($this->_password))
            return FALSE;

        $db = & new PHPWS_DB('user_authorization');
        $db->addWhere('username', $this->username);
        $db->delete();
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
        } else
            return TRUE;
    }

    function updateGroup()
    {
        $db = & new PHPWS_DB('users_groups');
        $db->addWhere('user_id', $this->getId());
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
        } else
            return TRUE;
    }


    function getUserGroup()
    {
        if (isset($this->_user_group))
            return $this->_user_group;

        $db = & new PHPWS_DB('users_groups');
        $db->addWhere('user_id', $this->getId());
        $db->addColumn('id');
        $result = $db->select('one');
        if (PEAR::isError($result))
            return $result;
        elseif (!isset($result))
            return PHPWS_Error::get(USER_ERR_MISSING_GROUP, 'users', 'getUserGroup');
        else
            return $result;
    }

    function disallow($message=NULL)
    {
        if (!isset($message)){
            $message = _('Improper permission level for action requested.');
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

    function getUserSetting($setting, $refresh=FALSE)
    {
        if (!isset($GLOBALS['User_Settings']) || $refresh == TRUE){
            unset($GLOBALS['User_Settings']);
            $GLOBALS['User_Settings'] = PHPWS_User::getSettings();
        }

        if (PEAR::isError($GLOBALS['User_Settings'])) {
            return $GLOBALS['User_Settings'];
        }

        return $GLOBALS['User_Settings'][$setting];
    }

    function loadPermissions($loadAll=TRUE)
    {
        if ($loadAll == TRUE){
            $groups = &$this->getGroups();
        } else {
            $groups[] = $this->getUserGroup();
        }

        $this->_permission = & new Users_Permission($groups);
    }

    function kill()
    {
        if (empty($this->id))
            return FALSE;

        $db = & new PHPWS_DB('users');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
    
        $user_group = & new PHPWS_Group($this->getUserGroup());
        $user_group->kill();
    
    }

    function savePermissions($key)
    {
        if (!PHPWS_Core::moduleExists($key->module)) {
            return PHPWS_Error::get(PHPWS_NO_MOD_FOUND, 'core', __CLASS__ . '::' . __FUNCTION__);
        }
      
        PHPWS_Core::initModClass('users', 'Permission.php');
        return Users_Permission::savePermissions($key);
    }

    function assignPermissions($module, $item_id=NULL, $format=TRUE)
    {
        if (!PHPWS_Core::moduleExists($module)) {
            return PHPWS_Error::get(PHPWS_NO_MOD_FOUND, 'core', __CLASS__ . '::' . __FUNCTION__);
        }

        PHPWS_Core::initModClass('users', 'Permission.php');
        return Users_Permission::assignPermissions($module, $item_id, $format);
    }

    function getPermissionLevel($module)
    {
        if ($this->isDeity())
            return UNRESTRICTED_PERMISSION;

        PHPWS_Core::initModClass('users', 'Permission.php');

        if (!isset($this->_permission))
            $this->loadPermissions();

        return $this->_permission->getPermissionLevel($module);
    }

    function getUserTpl()
    { 
        if ($this->isActive()) {
            $linkVar['command'] = 'deactivateUser';
            $links[] = PHPWS_Text::secureLink(_('Deactivate'), 'users', $linkVar);

            $template['ACTIVE'] =  _('Yes');
        } else {
            $linkVar['command'] = 'activateUser';
            $links[] = PHPWS_Text::secureLink(_('Activate'), 'users', $linkVar);

            $template['ACTIVE'] = _('No');
        }

        $logged = $this->getLastLogged('%c');

        if (empty($logged)) {
            $template['LAST_LOGGED'] =  _('Never');
        } else {
            $template['LAST_LOGGED'] = $logged;
        }

        $template['EMAIL'] = $this->getEmail(TRUE, TRUE);

    
        $linkVar['action'] = 'admin';
        $linkVar['user_id'] = $this->id;

        $jsvar['QUESTION'] = sprintf(_('Are you certain you want to delete the user &quot;%s&quot; permanently?'),
                                     $this->getUsername());
        $jsvar['ADDRESS']  = 'index.php?module=users&amp;action=admin&amp;command=deleteUser&amp;user_id='
            . $this->id . '&amp;authkey=' . Current_User::getAuthKey();
        $jsvar['LINK']     = _('Delete');
    
    
        $linkVar['command'] = 'editUser';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'users', $linkVar);

        $linkVar['command'] = 'setUserPermissions';
        $links[] = PHPWS_Text::secureLink(_('Permissions'), 'users', $linkVar);

        if (!$this->isDeity() && ($this->id != Current_User::getId())) {
            $links[] = Layout::getJavascript('confirm', $jsvar);
        }

        $template['ACTIONS'] = implode(' | ', $links);

        return $template;
    }

}

?>