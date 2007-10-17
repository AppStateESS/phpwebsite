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

PHPWS_Core::initModClass('users', 'Users.php');

if (!defined('ALLOW_DEITY_REMEMBER_ME')) {
    define('ALLOW_DEITY_REMEMBER_ME', false);
 }

class Current_User {

    /**
     * Initializes the User session
     */
    function init($id)
    {
        $_SESSION['User'] = new PHPWS_User($id);
        $_SESSION['User']->setLogged(true);
        Current_User::updateLastLogged();
        Current_User::getLogin();
    }

    function getUserObj()
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
    function allow($module, $subpermission=null, $item_id=0, $itemname=null, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
                return false;
        }
        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, false);
    }

    /**
     * Works the same as the allow function but confirms the user's authorization code
     *
     * @param  string   module             Name of the module checking
     * @param  string   subpermission      Name of the module permission to verify
     * @param  integer  item_id            Id of the item to verify
     * @param  string   itename            Name of the item permission
     * @param  boolean  unrestricted_only  If true, user must be have unrestricted 
     *                                     priviledges for that module regardless of 
     *                                     module, subpermission, or item id
     */
    function authorized($module, $subpermission=null, $item_id=0, $itemname=null, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
                return false;
        }

        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, true);
    }

    function allowedItem($module, $item_id, $itemname=null)
    {
        return $_SESSION['User']->allowedItem($module, $item_id, $itemname);
    }

    /**
     * Verifies the user is a deity and their authorization code is permitted
     */
    function deityAllow()
    {
        return $_SESSION['User']->deityAllow();
    }

    /**
     * sends a user to the 403 error page and logs a message (if specified)
     * to the security log
     * @param string  message  Message sent to log
     * @param boolean login    If true, then allow change to login
     */
    function disallow($message=null, $login=true)
    {
        if ($login && Current_User::requireLogin()) {
            return;
        } else {
            PHPWS_User::disallow($message);
        }
    }

    function getLogin()
    {
        PHPWS_Core::initModClass('users', 'Form.php');
        $login = User_Form::logBox();
        if (!empty($login)) {
            Layout::set($login, 'users', 'login_box', false);
        }
    }

    /**
     * returns true is currently logged user is a deity
     */
    function isDeity()
    {
        return $_SESSION['User']->isDeity();
    }

    function getId()
    {
        return $_SESSION['User']->getId();
    }

    function getAuthKey()
    {
        if (!isset($_SESSION['User'])) {
            return null;
        }
        return $_SESSION['User']->getAuthKey();
    }

    function verifyAuthKey()
    {
        return $_SESSION['User']->verifyAuthKey();
    }

    function getUnrestrictedLevels()
    {
        return $_SESSION['User']->getUnrestrictedLevels();
    }

    /**
     * Returns true if the user is restricted. Note that false will be
     * returned on unrestricted users AND users who do not have module
     * permission. User permission must be checked separately.
     * You may want to use !isUnrestricted instead.
     */
    function isRestricted($module)
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
    function isUser($id)
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
    function isUnrestricted($module)
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

    function updateLastLogged()
    {
        $db = new PHPWS_DB('users');
        $db->addWhere('id', $_SESSION['User']->getId());
        $db->addValue('last_logged', mktime());
        return $db->update();
    }

    function getUsername()
    {
        return $_SESSION['User']->getUsername();
    }

    function getDisplayName()
    {
        return $_SESSION['User']->getDisplayName();
    }

    function getEmail($html=false,$showAddress=false)
    {
        return $_SESSION['User']->getEmail($html,$showAddress);
    }

    function isLogged()
    {
        if (!isset($_SESSION['User'])) {
            $_SESSION['User'] = new PHPWS_User;
        }

        return $_SESSION['User']->isLogged();
    }

    function save()
    {
        return $_SESSION['User']->save();
    }

    function getPermissionLevel($module)
    {
        if ($_SESSION['User']->isDeity())
            return UNRESTRICTED_PERMISSION;

        return $_SESSION['User']->_permission->getPermissionLevel($module);
    }

    function giveItemPermission($key)
    {
        return Users_Permission::giveItemPermission(Current_User::getId(), $key);
    }

    function getCreatedDate()
    {
        return $_SESSION['User']->created;
    }

    function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    function getGroups()
    {
        if (empty($_SESSION['User']->_groups)) {
            return null;
        }
        return $_SESSION['User']->_groups;
    }

    function permissionMenu()
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

    function popupPermission($key_id, $label=null)
    {
        if (empty($label)) {
            $js_vars['label'] = dgettext('users', 'Permission');
        } else {
            $js_vars['label'] = strip_tags($label);
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
    function allowUsername($username)
    {
        if (preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/i', $username)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Logs in a user dependant on their authorization setting
     */
    function loginUser($username, $password)
    {
        if (!Current_User::allowUsername($username)) {
            return PHPWS_Error::get(USER_BAD_CHARACTERS, 'users', 'Current_User::loginUser');
        }

        $createUser = false;
        // First check if they are currently a user
        $user = new PHPWS_User;
        $db = new PHPWS_DB('users');
        $db->addWhere('username', strtolower($username));
        $result = $db->loadObject($user);

        if (PEAR::isError($result)) {
            return $result;
        }

        // if result is blank then check against the default authorization
        if ($result == false){
            $authorize = PHPWS_User::getUserSetting('default_authorization');
            $createUser = true;
            $user->setUsername($username);
        } else {
            if (!$user->approved) {
                return PHPWS_Error::get(USER_NOT_APPROVED, 'users', 'Current_User::loginUser');
            }
            $authorize = $user->getAuthorize();
        }

        if (empty($authorize)) {
            return PHPWS_Error::get(USER_AUTH_MISSING, 'users', 'Current_User::loginUser');
        }

        $result = Current_User::authorize($authorize, $user, $password);

        if (PEAR::isError($result)){
            return $result;
        }

        if ($result == true){
            if ($createUser == true){
                $result = $user->setUsername($username);

                if (PEAR::isError($result)){
                    return $result;
                }

                $user->setAuthorize($authorize);
                $user->setActive(true);
                $user->setApproved(true);

                if (function_exists('post_authorize')) {
                    post_authorize($user);
                }

                $user->save();
            }

            if (!$user->active) {
                return PHPWS_Error::get(USER_DEACTIVATED, 'users', 'Current_User:loginUser', $user->username);
            }

            $user->login();
            $_SESSION['User'] = $user;
            return true;
        } else {
            return false;
        }
    }

    function authorize($authorize, &$user, $password)
    {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->setIndexBy('id');
        $result = $db->select();

        if (empty($result)) {
            return false;
        }

        if (isset($result[$authorize])) { 
            extract($result[$authorize]);
            $file = PHPWS_SOURCE_DIR . 'mod/users/scripts/' . $filename;

            if(!is_file($file)){
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize', $file);
                return false;
            }

            require_once $file;

            if (function_exists('authorize')) {
                $result = authorize($user, $password);
                return $result;
            } else {
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize');
                return false;
            }
        } else {
            return false;
        }

        return $result;
    }
    
    function requireLogin()
    {
        if (Current_User::isLogged()) {
            return false;
        }
        PHPWS_Core::bookmark();
        $url = 'index.php?module=users&action=user&command=login_page';
        PHPWS_Core::reroute($url);
    }

    function rememberLogin()
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
        } elseif (PEAR::isError($result)) {
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
        } elseif (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        $_SESSION['User']->login();
        return true;
    }

    function allowRememberMe()
    {
        if ( PHPWS_Settings::get('users', 'allow_remember') &&
             ( !Current_User::isDeity() || ALLOW_DEITY_REMEMBER_ME ) ) {
            return true;
        } else {
            return false;
        }
    }

}

?>