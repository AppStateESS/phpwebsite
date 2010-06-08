<?php

/**
 * Class containing all user information
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::initModClass('users', 'Permission.php');
core\Core::initModClass('users', 'Authorization.php');
core\Core::requireConfig('users');
require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';

if (!defined('ALLOWED_USERNAME_CHARACTERS')) {
	define('ALLOWED_USERNAME_CHARACTERS'. '\w');
}

class PHPWS_User {
	public $id             = 0;
	public $username       = null;
	public $deity          = false;
	public $active         = true;
	// id of authorizing file for user
	public $authorize      = 0;
	public $last_logged    = 0;
	public $log_count      = 0;
	public $created        = 0;
	public $updated        = 0;
	// if true, they have been approved to log in
	public $approved       = false;
	public $email          = null;
	public $display_name   = null;

	public $_password        = null;
	public $_groups          = null;
	public $_permission      = null;
	public $_user_group      = null;
	public $auth_key         = null;
	private $salt_base       = null;
	// Indicates whether this is a logged in user
	public $_logged          = false;
	public $_prev_username   = null;
	public $auth_script      = null;
	public $auth_path        = null;
	public $auth_name        = null;

	public function __construct($id=0)
	{
		if(!$id) {
			$this->authorize = PHPWS_User::getUserSetting('default_authorization');
			$this->loadScript();
			return;
		}
		$this->setId($id);
		$result = $this->init();

		if (core\Error::isError($result)) {
			core\Error::log($result);
		}

		if ($result) {
			$this->loadScript();
		}
	}

	public function init()
	{
		$db = new \core\DB('users');
		$result = $db->loadObject($this);

		if (core\Error::isError($result)) {
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


	public function setId($id)
	{
		$this->id = (int)$id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function login()
	{
		$this->setLogged(true);
		$this->setLastLogged(time());
		$this->addLogCount();
		$this->makeAuthKey();
		$this->updateOnly();
		$this->loadUserGroups();
		$this->loadPermissions();
	}

	public function isDuplicateDisplayName($display_name, $id=0)
	{
		if (empty($display_name)) {
			return false;
		}

		$DB = new \core\DB('users');
		$DB->addWhere('display_name', $display_name, '=', null, '1');
		$DB->addWhere('username', $display_name, '=', 'or', '1');
		if ($id) {
			$DB->addWhere('id', $id, '!=', 'and');
		}

		$result = $DB->select('one');
		if (core\Error::isError($result)) {
			return $result;
		} else {
			return (bool)$result;
		}
	}

	public function isDuplicateUsername($username, $id=0)
	{
		$DB = new \core\DB('users');
		$DB->addWhere('username', $username);
		if ($id) {
			$DB->addWhere('id', $id, '!=');
		}

		$result = $DB->select('one');

		if (core\Error::isError($result)) {
			return $result;
		} else {
			return (bool)$result;
		}
	}

	public function isDuplicateGroup($name, $id=0)
	{
		$DB = new \core\DB('users_groups');
		$DB->addWhere('name', $name);
		if ($id) {
			$DB->addWhere('user_id', $id, '!=');
		}

		$result = $DB->select('one');
		if (core\Error::isError($result)) {
			return $result;
		} else {
			return (bool)$result;
		}
	}

	public function isDuplicateEmail()
	{
		if (empty($this->email))
		return false;

		$DB = new \core\DB('users');
		$DB->addWhere('email', $this->email);
		if ($this->id) {
			$DB->addWhere('id', $this->id, '!=');
		}

		$result = $DB->select('one');
		if (core\Error::isError($result)) {
			return $result;
		} else {
			return (bool)$result;
		}
	}


	public function setUsername($username)
	{
		$username = strtolower($username);
		if (empty($username) || !Current_User::allowUsername($username)) {
			return \core\Error::get(USER_ERR_BAD_USERNAME, 'users',
                                    'setUsername', $username);
		}

		if (strlen($username) < USERNAME_LENGTH) {
			return \core\Error::get(USER_ERR_BAD_USERNAME, 'users',
                                    'setUsername', $username);
		}

		if ($this->isDuplicateUsername($username, $this->id) ||
		$this->isDuplicateDisplayName($username, $this->id)) {
			return \core\Error::get(USER_ERR_DUP_USERNAME, 'users',
                                    'setUsername', $username); ;
		}

		if ($this->isDuplicateGroup($username, $this->id)) {
			return \core\Error::get(USER_ERR_DUP_GROUPNAME, 'users',
                                    'setUsername', $username); ;
		}

		$this->username = $username;

		return true;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setPassword($password, $hashPass=true)
	{
		if (empty($password)) {
			core\Error::log(USER_PASSWORD_BLANK, 'users', 'PHPWS_User::setPassword');
		}

		if ($hashPass) {
			$this->_password = md5($this->username . $password);
		} else {
			$this->_password = $password;
		}
	}

	public function checkPassword($pass1, $pass2)
	{
		if (empty($pass1) || empty($pass2)) {
			return \core\Error::get(USER_ERR_PASSWORD_LENGTH, 'users', 'checkPassword');
		}
		elseif ($pass1 != $pass2) {
			return \core\Error::get(USER_ERR_PASSWORD_MATCH, 'users', 'checkPassword');
		}
		elseif(strlen($pass1) < PASSWORD_LENGTH) {
			return \core\Error::get(USER_ERR_PASSWORD_LENGTH, 'users', 'checkPassword');
		}
		elseif(preg_match('/(' . implode('|', unserialize(BAD_PASSWORDS)) . ')/i', $pass1)) {
			return \core\Error::get(USER_ERR_PASSWORD_EASY, 'users', 'checkPassword');
		}
		else {
			return true;
		}
	}

	public function getPassword()
	{
		return $this->_password;
	}

	public function setLogged($status)
	{
		$this->_logged = $status;
	}

	public function isLogged()
	{
		return (bool)$this->_logged;
	}

	public function setLastLogged($time)
	{
		$this->last_logged = $time;
	}

	public function getLastLogged($mode=null)
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

	public function addLogCount()
	{
		$this->log_count++;
	}

	public function getLogCount()
	{
		return $this->log_count;
	}

	public function isUser()
	{
		return (bool)$this->id;
	}

	public function setDeity($deity)
	{
		$this->deity = (bool)$deity;
	}

	public function isDeity()
	{
		return $this->deity;
	}

	public function setActive($active)
	{
		$this->active = (bool)$active;
	}

	public function isActive()
	{
		return (bool)$this->active;
	}

	public function setAuthorize($authorize)
	{
		$this->authorize = (int)$authorize;
	}

	public function getAuthorize()
	{
		return $this->authorize;
	}

	public function setApproved($approve)
	{
		$this->approved = (bool)$approve;
	}

	public function isApproved()
	{
		return (bool)$this->approved;
	}

	public function setEmail($email)
	{
		$this->email = $email;

		if (!core\Text::isValidInput($email, 'email')) {
			return \core\Error::get(USER_ERR_BAD_EMAIL, 'users', 'setEmail');
		}

		if ($this->isDuplicateEmail()) {
			return \core\Error::get(USER_ERR_DUP_EMAIL, 'users', 'setEmail');
		}

		return true;
	}

	public function getEmail($html=false, $showAddress=false)
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

	public function setDisplayName($name)
	{
		if (empty($name)) {
			$this->display_name = $this->username;
			return true;
		}

		if (UTF8_MODE) {
			$preg = '/[^\w\-\s\pL]+$/ui';
		} else {
			$preg = '/[^\w\-\s]+$/ui';
		}


		if (preg_match($preg, $name)) {
			return \core\Error::get(USER_ERR_BAD_DISPLAY_NAME, 'users',
                                    'setUsername', $name);
		}

		if (strlen($name) < DISPLAY_NAME_LENGTH) {
			return \core\Error::get(USER_ERR_BAD_DISPLAY_NAME, 'users',
                                    'setUsername', $name);
		}

		if ($this->isDuplicateUsername($name, $this->id) ||
		$this->isDuplicateDisplayName($name, $this->id)) {
			return \core\Error::get(USER_ERR_DUP_USERNAME, 'users',
                                    'setDisplayName', $name); ;
		}

		$this->display_name = $name;

		return true;
	}


	public function getDisplayName()
	{
		if (empty($this->display_name)) {
			return $this->username;
		} else {
			return $this->display_name;
		}
	}

	public function loadUserGroups()
	{
		$group = $this->getUserGroup();

		if (core\Error::isError($group)){
			core\Error::log($group);
			return false;
		}

		$this->_user_group = $groupList[] = $group;

		$DB = new \core\DB('users_members');
		$DB->addWhere('member_id', $group);
		$DB->addColumn('group_id');
		$result = $DB->select('col');

		if (core\Error::isError($group)){
			core\Error::log($group);
			return false;
		}

		if (is_array($result))
		$groupList = array_merge($result, $groupList);

		$this->setGroups($groupList);
		return true;
	}


	public function setGroups($groups)
	{
		$this->_groups = $groups;
	}

	public function getGroups()
	{
		return $this->_groups;
	}

	public function canChangePassword()
	{
		return ($this->authorize == \core\Settings::get('users', 'local_script'));
	}

	public function verifyAuthKey($salt_value=null)
	{
		if ($salt_value && !is_string($salt_value)) {
			trigger_error('Salt value must be a string', E_USER_ERROR);
		}

		if (!isset($_REQUEST['authkey']) || $_REQUEST['authkey'] !== $this->getAuthKey($salt_value)) {
			return false;
		}

		return true;
	}

	public function deityAllow()
	{
		if (!$this->verifyAuthKey() || !$this->isDeity()) {
			return false;
		}
		return true;
	}

	public function allowedItem($module, $item_id, $itemname=null)
	{
		return $this->_permission->allowedItem($module, $item_id, $itemname);
	}

	public function allow($module, $subpermission=null, $item_id=null, $itemname=null, $verify=false)
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

		core\Core::initModClass('users', 'Permission.php');
		return $this->_permission->allow($module, $subpermission, $item_id, $itemname);
	}

	/**
	 * Crutch function for versions prior to 0.x
	 */
	public function allow_access($itemName, $subpermission=null, $item_id=null)
	{
		return $this->allow($itemName, $subpermission, $item_id);
	}


	public function save()
	{
		core\Core::initModClass('users', 'Group.php');

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
		if (core\Error::isError($result)) {
			return $result;
		}

		if ($result == true) {
			return \core\Error::get(USER_ERR_DUP_USERNAME, 'users', 'save');
		}

		$result = $this->isDuplicateEmail();
		if (core\Error::isError($result)) {
			return $result;
		}

		if ($result == true) {
			return \core\Error::get(USER_ERR_DUP_EMAIL, 'users', 'save');
		}

		$result = $this->isDuplicateGroup($this->username, $this->id);
		if (core\Error::isError($result)) {
			return $result;
		}

		if ($result == true) {
			return \core\Error::get(USER_ERR_DUP_GROUPNAME, 'users', 'save');
		}

		if (empty($this->display_name)) {
			$this->display_name = $this->username;
		}

		if (!isset($this->authorize)) {
			$this->authorize = $this->getUserSetting('default_authorization');
		}

		if ($newUser == true) {
			$this->created = time();
		} else {
			$this->updated = time();
		}

		$db = new \core\DB('users');
		$result = $db->saveObject($this);

		if (core\Error::isError($result)){
			core\Error::log($result);
			return \core\Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
		}

		if ($this->authorize > 0) {
			if ($this->authorize == \core\Settings::get('users', 'local_script')) {
				$result = $this->saveLocalAuthorization();
				if (core\Error::isError($result)) {
					core\Error::log($result);
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

	public function updateOnly()
	{
		$db = new \core\DB('users');
		$result = $db->saveObject($this);

		if (core\Error::isError($result)){
			core\Error::log($result);
			return \core\Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
		}

		return $result;
	}

	public function makeAuthKey()
	{
		$key = rand();
		$this->salt_base = $key . time();
		$this->auth_key = md5($this->username . $this->salt_base);
	}


	public function getAuthKey($salt_value=null)
	{
		if ($salt_value && !is_string($salt_value)) {
			trigger_error('Salt value must be a string', E_USER_ERROR);
		}

		if (empty($salt_value)) {
			return $this->auth_key;
		} else {
			return md5($salt_value . $this->salt_base);
		}
	}

	public function saveLocalAuthorization()
	{
		if (empty($this->username) || empty($this->_password)) {
			return false;
		}

		$db = new \core\DB('user_authorization');
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

	public function createGroup()
	{
		$group = new PHPWS_Group;
		$group->setName($this->getUsername());
		$group->setUserId($this->getId());
		$group->setActive($this->isActive());
		$result = $group->save();

		if (core\Error::isError($result)){
			core\Error::log($result);
			$this->kill();
			return \core\Error::get(USER_ERR_USER_NOT_SAVED, 'users', 'save');
		} else {
			$this->_user_group = $group->id;
			return true;
		}
	}

	public function updateGroup()
	{
		$db = new \core\DB('users_groups');
		$db->addWhere('user_id', $this->id);
		$db->addColumn('id');
		$result = $db->select('one');

		if (core\Error::isError($result)){
			core\Error::log($result);
			return \core\Error::get(USER_ERROR, 'users', 'updateGroup');
		}

		if (empty($result)) {
			$group = new PHPWS_Group;
			$group->setUserId($this->id);
		} else {
			$group = new PHPWS_Group($result);
		}

		$group->setName($this->getUsername());
		$group->setActive($this->isActive());

		$result = $group->save();
		if (core\Error::isError($result)){
			core\Error::log($result);
			$this->kill();
			return \core\Error::get(USER_ERROR, 'users', 'updateGroup');
		} else {
			return true;
		}
	}


	public function getUserGroup()
	{
		if (isset($this->_user_group)) {
			return $this->_user_group;
		}

		$db = new \core\DB('users_groups');
		$db->addWhere('user_id', $this->getId());
		$db->addColumn('id');

		$result = $db->select('one');

		if (core\Error::isError($result)) {
			return $result;
		} elseif (!isset($result)) {
			return \core\Error::get(USER_ERR_MISSING_GROUP, 'users', 'getUserGroup', $this->getId());
		} else {
			return $result;
		}
	}

	public static function disallow($message=null)
	{
		if (!isset($message)){
			$message = dgettext('users', 'Improper permission level for action requested.');
		}
		Security::log($message);
		core\Core::errorPage('403');
	}


	public function getSettings()
	{
		$DB = new \core\DB('users_config');
		return $DB->select('row');
	}

	public function resetUserSettings()
	{
		unset($GLOBALS['User_Settings']);
	}

	public static function getUserSetting($setting, $refresh=false)
	{
		return \core\Settings::get('users', $setting);
	}

	public function loadPermissions($loadAll=true)
	{
		if ($loadAll == true){
			$groups = $this->getGroups();
		} else {
			$groups[] = $this->getUserGroup();
		}

		$this->_permission = new Users_Permission($groups);
	}

	public function kill()
	{
		if (!$this->id) {
			return false;
		}

		$db = new \core\DB('users');
		$db->addWhere('id', $this->id);
		$result = $db->delete();
		if (core\Error::isError($result)) {
			return $result;
		}

		$this->removeAssociations();

		if ($this->authorize == LOCAL_AUTHORIZATION) {
			$db2 = new \core\DB('user_authorization');
			$db2->addWhere('username', $this->username);
			$result = $db2->delete();
			if (core\Error::isError($result)) {
				return $result;
			}
		}

		$user_group = new PHPWS_Group($this->getUserGroup());
		$user_group->kill();
	}


	/**
	 * Looks for the user.php in an installed module's inc folder.
	 * If found, it runs the public function within.
	 */
	public function removeAssociations()
	{
		$modules = \core\Core::getModules(true, true);
		foreach ($modules as $mod) {
			$file = sprintf('%smod/%s/inc/remove_user.php', PHPWS_SOURCE_DIR, $mod);
			if (is_file($file)) {
				require_once $file;
				$function_name = $mod . '_remove_user';
				if (function_exists($function_name)) {
					$function_name($this->id);
				}
			}
		}
	}

	public function savePermissions($key)
	{
		if (!is_object($key) || strtolower(get_class($key) == 'key')) {
			return false;
		}

		if (!core\Core::moduleExists($key->module)) {
			return \core\Error::get(PHPWS_NO_MOD_FOUND, 'core', __CLASS__ . '::' . __FUNCTION__);
		}

		core\Core::initModClass('users', 'Permission.php');
		return Users_Permission::savePermissions($key);
	}

	public static function getAllGroups()
	{
		core\Core::initModClass('users', 'Action.php');
		return User_Action::getGroups('group');
	}

	public function getUnrestrictedLevels()
	{
		if (!isset($this->_permission)) {
			$this->loadPermissions();
		}

		if (!empty($this->_permission->levels)) {
			return array_keys($this->_permission->levels, UNRESTRICTED_PERMISSION);
		}
	}


	public function getPermissionLevel($module)
	{
		if ($this->isDeity()) {
			return UNRESTRICTED_PERMISSION;
		}

		core\Core::initModClass('users', 'Permission.php');

		if (!isset($this->_permission)) {
			$this->loadPermissions();
		}

		return $this->_permission->getPermissionLevel($module);
	}

	public function getUserTpl()
	{
		// Don't let a deity change their deity status
		// Don't let non-deities change status

		if (Current_User::isDeity() && !Current_User::isUser($this->id)) {
			if ($this->isDeity()) {
				$dvars['QUESTION'] = dgettext('users', 'Are you sure you want to remove deity status?');
				$dvars['ADDRESS']  = \core\Text::linkAddress('users', array('action'=>'admin', 'command'=>'mortalize_user', 'user_id'=>$this->id), 1);
				$dvars['LINK']     = sprintf('<img src="%smod/users/img/deity.gif" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('users', 'Deity'));
				$links[] = javascript('confirm', $dvars);
			} else {
				$dvars['QUESTION'] = dgettext('users', 'Are you sure you want to deify this user?');
				$dvars['ADDRESS']  = \core\Text::linkAddress('users', array('action'=>'admin', 'command'=>'deify_user', 'user_id'=>$this->id), 1);
				$dvars['LINK']     = sprintf('<img src="%smod/users/img/man.gif" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('users', 'Mortal'));
				$links[] = javascript('confirm', $dvars);
			}
		}

		$linkVar['action'] = 'admin';
		$linkVar['user_id'] = $this->id;

		if ($this->isActive()) {
			if (!$this->deity) {
				$linkVar['command'] = 'deactivateUser';
				$template['ACTIVE'] = \core\Text::secureLink(dgettext('users', 'Yes'), 'users', $linkVar, null, dgettext('users', 'Deactivate this user'));
			} else {
				$template['ACTIVE'] =  dgettext('users', 'Yes');
			}
		} else {
			if (!$this->deity) {
				$linkVar['command'] = 'activateUser';
				$template['ACTIVE'] =  \core\Text::secureLink(dgettext('users', 'No'), 'users', $linkVar, null, dgettext('users', 'Activate this user'));
			} else {
				$template['ACTIVE'] = dgettext('users', 'No');
			}
		}


		$logged = $this->getLastLogged('%c');

		if (empty($logged)) {
			$template['LAST_LOGGED'] =  dgettext('users', 'Never');
		} else {
			$template['LAST_LOGGED'] = $logged;
		}

		$template['EMAIL'] = $this->getEmail(true, true);


		$jsvar['QUESTION'] = sprintf(dgettext('users', 'Are you certain you want to delete the user &quot;%s&quot; permanently?'),
		$this->getUsername());
		$link = new \core\Link(null, 'users', array('action'=>'admin',
                                                    'command'=>'deleteUser',
                                                    'user_id'=> $this->id), true);
		$link->setSalted();
		$jsvar['ADDRESS'] = $link->getAddress();
		$jsvar['LINK']    = \core\Icon::show('delete');

		$linkVar['command'] = 'editUser';
		$links[] = \core\Text::secureLink(core\Icon::show('edit'), 'users', $linkVar);

		$linkVar['command'] = 'setUserPermissions';
		$links[] = \core\Text::secureLink(core\Icon::show('permission'), 'users', $linkVar);

		if (!$this->isDeity() && ($this->id != Current_User::getId())) {
			$links[] = Layout::getJavascript('confirm', $jsvar);
		}

		$template['ACTIONS'] = implode('', $links);

		if ($this->deity && !Current_User::isDeity()) {
			unset($template['ACTIONS']);
		}
		return $template;
	}

	public function registerPermissions($module, &$content)
	{
		return Users_Permission::registerPermissions($module, $content);
	}

	/**
	 * Loads the script file for authorization
	 */
	public function loadScript()
	{
		$db = new \core\DB('users_auth_scripts');
		$db->addWhere('id', $this->authorize);
		$db->addColumn('filename');
		$filename = $db->select('one');
		if (core\Error::logIfError($filename)) {
			return;
		}

		$this->auth_script = $filename;
		$this->auth_path   = PHPWS_SOURCE_DIR . 'mod/users/scripts/' . $filename;
		$this->auth_name   = preg_replace('/\.php$/i', '', $filename);

		if (!is_file($this->auth_path)) {
			core\Error::log(USER_ERR_MISSING_AUTH, 'users', 'User_Authorization', $this->auth_path);
			return false;
		} else {
			return true;
		}
	}
}

?>
