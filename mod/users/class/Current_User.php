<?php
/**
 * The Current_User class is a shortcut to the Users class.
 * When using the Current_User you are acting on the user currently
 * logged into the system. Current_User is actually pathing through
 * the current user session.
 *
 * @author  Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('users', 'Authorization.php');
PHPWS_Core::initModClass('users', 'Users.php');

if (!defined('ALLOW_DEITY_REMEMBER_ME')) {
    define('ALLOW_DEITY_REMEMBER_ME', false);
}

final class Current_User {
    /**
     * Initializes the User session
     */
    public static function init($id=0)
    {
        if ($id) {
            $_SESSION['User'] = new PHPWS_User($id);
            $_SESSION['User']->setLogged(true);
            Current_User::updateLastLogged();
            Current_User::getLogin();
        } else {
            $_SESSION['User'] = new PHPWS_User;
        }
    }

    public static function getUserObj()
    {
        return $_SESSION['User'];
    }

    /**
     * Determines if a user is allowed to use a specific module, permission, and/or item
     *
     * @param  string   module             Name of the module checking
     * @param  string   subpermission      Name of the module permission to verify
     * @param  integer  item_id            Id of the item to verify
     * @param  string   itename            Name of the item permission
     * @param  boolean  unrestricted_only  If true, user must have unrestricted
     *                                     priviledges for that module regardless of
     *                                     module, subpermission, or item id
     */
    public static function allow($module, $subpermission=null, $item_id=0, $itemname=null, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
            return false;
        }

        if (!isset($_SESSION['User'])) {
            return false;
        }

        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, false);
    }

    /**
     * Works like authorized, but checks for a salted authkey
     * Won't work on posts yet.
     */
    public static function secured($module, $subpermission=null, $item_id=0, $itemname=null, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
            return false;
        }

        if (!isset($_SESSION['User'])) {
            return false;
        }

        return Current_User::verifySaltedUrl() && $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname);
    }


    /**
     * Works the same as the allow static function but confirms the user's authorization code
     *
     * @param  string   module             Name of the module checking
     * @param  string   subpermission      Name of the module permission to verify
     * @param  integer  item_id            Id of the item to verify
     * @param  string   itename            Name of the item permission
     * @param  boolean  unrestricted_only  If true, user must be have unrestricted
     *                                     priviledges for that module regardless of
     *                                     module, subpermission, or item id
     */
    public static function authorized($module, $subpermission=null, $item_id=0, $itemname=null, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
            return false;
        }

        if (!isset($_SESSION['User'])) {
            return false;
        }

        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, true);
    }

    public static function allowedItem($module, $item_id, $itemname=null)
    {
        return $_SESSION['User']->allowedItem($module, $item_id, $itemname);
    }

    /**
     * Verifies the user is a deity and their authorization code is permitted
     */
    public static function deityAllow()
    {
        return $_SESSION['User']->deityAllow();
    }

    /**
     * sends a user to the 403 error page and logs a message (if specified)
     * to the security log
     * @param string  message  Message sent to log
     * @param boolean login    If true, then allow change to login
     */
    public static function disallow($message=null, $login=true)
    {
        if ($login && Current_User::requireLogin()) {
            return;
        } else {
            PHPWS_User::disallow($message);
        }
    }

    public static function getLogin()
    {
        $user = $_SESSION['User'];
        $auth = Current_User::getAuthorization();

        // If the current user is not verified then
        // either force to authentication page or clear the user session
        if (!$auth->verify()) {
            // reset user session is set
            if ($user->id) {
                Current_User::init();
            }

            // if they are force login, the below will send them there
            // and we will end getLogin
            // if not forced, then we just continue;
            $auth->forceLogin();
        }

        PHPWS_Core::initModClass('users', 'Form.php');
        $login = User_Form::logBox();
        if (!empty($login)) {
            Layout::set($login, 'users', 'login_box', false);
        }
    }

    /**
     * returns true is currently logged user is a deity
     */
    public static function isDeity()
    {
        return $_SESSION['User']->isDeity();
    }

    public static function getId()
    {
        return $_SESSION['User']->getId();
    }

    public static function getAuthKey($salt_value=null)
    {
        if (!isset($_SESSION['User'])) {
            return null;
        }

        return $_SESSION['User']->getAuthKey($salt_value);
    }

    public static function verifyAuthKey($check_salted=false)
    {
        return $_SESSION['User']->verifyAuthKey($check_salted);
    }

    public static function verifySaltedUrl()
    {
        $val = PHPWS_Text::getGetValues();
        unset($val['module']);
        unset($val['authkey']);
        unset($val['owpop']);

        $serial_url = str_replace(' ', '+', serialize($val));
        return Current_User::verifyAuthKey($serial_url);
    }


    public static function getUnrestrictedLevels()
    {
        return $_SESSION['User']->getUnrestrictedLevels();
    }

    /**
     * Returns true if the user is restricted. Note that false will be
     * returned on unrestricted users AND users who do not have module
     * permission. User permission must be checked separately.
     * You may want to use !isUnrestricted instead.
     */
    public static function isRestricted($module)
    {
        if (Current_User::isDeity()) {
            return false;
        }

        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == RESTRICTED_PERMISSION ? true : false;
    }

    /**
     * An id of 0 will ALWAYS return false.
     *
     * @param integer id
     * @return True, if current user's id equals the parameter
     */
    public static function isUser($id)
    {
        if (!$id) {
            return false;
        }
        return ($_SESSION['User']->id == $id) ? true : false;
    }

    /**
     * Returns true is the user has unrestricted access to a module.
     * Unlike isRestricted, user must be logged in and have module access
     */
    public static function isUnrestricted($module)
    {
        if (Current_User::isDeity()) {
            return true;
        }

        if (empty($module)) {
            return false;
        }

        if (!Current_User::allow($module)) {
            return false;
        }

        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == UNRESTRICTED_PERMISSION ? true : false;
    }

    public static function updateLastLogged()
    {
        $db = new PHPWS_DB('users');
        $db->addWhere('id', $_SESSION['User']->getId());
        $db->addValue('last_logged', time());
        return $db->update();
    }

    public static function getUsername()
    {
        return $_SESSION['User']->getUsername();
    }

    public static function getDisplayName()
    {
        return $_SESSION['User']->getDisplayName();
    }

    public static function getEmail($html=false,$showAddress=false)
    {
        return $_SESSION['User']->getEmail($html,$showAddress);
    }

    public static function isLogged()
    {
        if (!isset($_SESSION['User'])) {
            $_SESSION['User'] = new PHPWS_User;
        }

        return $_SESSION['User']->isLogged();
    }

    public static function save()
    {
        return $_SESSION['User']->save();
    }

    public static function getPermissionLevel($module)
    {
        if ($_SESSION['User']->isDeity())
        return UNRESTRICTED_PERMISSION;

        return $_SESSION['User']->_permission->getPermissionLevel($module);
    }

    public static function giveItemPermission($key)
    {
        return Users_Permission::giveItemPermission(Current_User::getId(), $key);
    }

    public static function getCreatedDate()
    {
        return $_SESSION['User']->created;
    }

    public static function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getGroups()
    {
        if (empty($_SESSION['User']->_groups)) {
            return null;
        }
        return $_SESSION['User']->_groups;
    }

    public static function permissionMenu()
    {
        $key = Key::getCurrent();

        if (empty($key) || $key->isDummy() || empty($key->edit_permission)) {
            return;
        }

        if (Current_User::isUnrestricted($key->module) &&
        Current_User::allow($key->module, $key->edit_permission)) {

            if (!javascriptEnabled()) {
                $tpl = User_Form::permissionMenu($key);
                $content = PHPWS_Template::process($tpl, 'users', 'forms/permission_menu.tpl');
                Layout::add($content, 'users', 'permissions');
            } else {
                $links[] = Current_User::popupPermission($key->id, sprintf(dgettext('users', 'Set permissions'), $key->title));
                MiniAdmin::add('users', $links);
            }
        }
    }

    public static function popupPermission($key_id, $label=null, $mode=null)
    {
        if (empty($label)) {
            $label = dgettext('users', 'Permission');
        } else {
            $label = strip_tags($label);
        }

        switch($mode) {
            case 'icon':
                $js_vars['label'] = Icon::show('permission', $label);
                break;

            default:
                $js_vars['label'] = & $label;
        }

        $js_vars['width'] = 350;
        $js_vars['height'] = 350;

        $js_vars['address'] = sprintf('index.php?module=users&action=popup_permission&key_id=%s&authkey=%s',$key_id, Current_User::getAuthKey());

        return javascript('open_window', $js_vars);
    }

    /**
     * Returns true if the supplied username only contains characters defined
     * by the ALLOWED_USERNAME_CHARACTERS variable.
     */
    public static function allowUsername($username)
    {
        return !preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/i', $username);
    }

    /**
     * Logs in a user dependant on their authorization setting
     */
    public static function loginUser($username, $password=null)
    {
        if (!Current_User::allowUsername($username)) {
            return PHPWS_Error::get(USER_BAD_CHARACTERS, 'users', 'Current_User::loginUser');
        }

        // First check if they are currently a user
        $user = new PHPWS_User;
        $db = new PHPWS_DB('users');
        $db->addWhere('username', strtolower($username));
        $result = $db->loadObject($user);

        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($result == false) {
            if (PHPWS_Error::logIfError($user->setUsername($username))) {
                return false;
            }
        } else {
            // This user is in the local database
            if (!$user->approved) {
                return PHPWS_Error::get(USER_NOT_APPROVED, 'users', 'Current_User::loginUser');
            }
            if (!$user->loadScript()) {
                Layout::add(dgettext('users', 'Could not load authentication script. Please contact site administrator.'));
                return false;
            }
        }

        if (!Current_User::loadAuthorization($user)) {
            Layout::add(dgettext('users', 'Could not load authentication script. Please contact site administrator.'));
            return false;
        }

        $auth = Current_User::getAuthorization();
        $auth->setPassword($password);
        $result = $auth->authenticate();

        if (PHPWS_Error::isError($result)){
            return $result;
        }

        if ($result == true) {
            // If the user id is zero and the authorization wants a new
            // user created
            if (!$user->id && $auth->create_new_user) {
                $user->setActive(true);
                $user->setApproved(true);
                $auth->createUser();
                $user->save();
                PHPWS_Core::initModClass('users', 'Action.php');
                User_Action::assignDefaultGroup($user);
            }


            if (!$user->active) {
                return PHPWS_Error::get(USER_DEACTIVATED, 'users', 'Current_User:loginUser', $user->username);
            }

            if ($auth->localUser()) {
                $user->login();
            }

            unset($_SESSION['User']);
            $_SESSION['User'] = $user;
            return true;
        } else {
            return false;
        }
    }

    public static function requireLogin()
    {
        if (Current_User::isLogged()) {
            return false;
        }
        PHPWS_Core::bookmark(false);
        $auth = Current_User::getAuthorization();
        if (!empty($auth->login_url)) {
            $url = $auth->login_url;
        } else {
            $url = 'index.php?module=users&action=user&command=login_page';
        }
        PHPWS_Core::reroute($url);
    }

    public static function rememberLogin()
    {
        if (!isset($_SESSION['User'])) {
            return false;
        }

        $remember = PHPWS_Cookie::read('remember_me');
        if (!$remember) {
            return false;
        }

        $rArray =  @unserialize($remember);

        if (!is_array($rArray)) {
            return false;
        }
        if (!isset($rArray['username']) || !isset($rArray['password'])) {
            return false;
        }

        if (preg_match('/\W/', $rArray['password'])) {
            return false;
        }

        $username = strtolower($rArray['username']);
        if (preg_match('/\'|"/', html_entity_decode($username, ENT_QUOTES))) {
            Security::log(dgettext('users', 'User tried to login using Remember Me with a malformed cookie.'));
            return false;
        }

        $db = new PHPWS_DB('user_authorization');
        $db->addWhere('username', $username);
        $db->addWhere('password', $rArray['password']);
        $result = $db->select('row');

        if (!$result) {
            return false;
        } elseif (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        $db2 = new PHPWS_DB('users');
        $db2->addWhere('username', $username);
        $db2->addWhere('approved', 1);
        $db2->addWhere('active', 1);
        if (!ALLOW_DEITY_REMEMBER_ME) {
            $db2->addWhere('deity', 0);
        }
        $result = $db2->loadObject($_SESSION['User']);

        if (!$result) {
            return false;
        } elseif (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        $_SESSION['User']->login();
        return true;
    }

    public static function allowRememberMe()
    {
        if ( PHPWS_Settings::get('users', 'allow_remember') &&
        ( !Current_User::isDeity() || ALLOW_DEITY_REMEMBER_ME ) ) {
            return true;
        } else {
            return false;
        }
    }

    public static function loadAuthorization(PHPWS_User $user)
    {
        if (!is_file($user->auth_path)) {
            return false;
        }
        require_once $user->auth_path;
        $class_name = $user->auth_name . '_authorization';
        if (!class_exists($class_name)) {
            PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'Current_User::loadAuthorization', $user->auth_path);
            return false;
        }
        $GLOBALS['User_Authorization'] = new $class_name($user);
        return true;
    }

    public static function getAuthorization()
    {
        return $GLOBALS['User_Authorization'];
    }

    public static function isLocalUser()
    {
        $auth = Current_User::getAuthorization();
        return $auth->local_user;
    }
}

?>