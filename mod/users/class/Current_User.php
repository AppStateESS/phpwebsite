<?php
  /**
   * The Current_User class is a shortcut to the Users class.
   * When using the Current_User you are acting on the user currently
   * logged into the system. Current_User is actually pathing through
   * the current user session.
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('users', 'Users.php');

class Current_User {

    function init($id)
    {
        $_SESSION['User'] = new PHPWS_User($id);
        $_SESSION['User']->setLogged(TRUE);
        Current_User::updateLastLogged();
        Current_User::getLogin();
    }
  
    function allow($module, $subpermission=NULL, $item_id=NULL, $itemname=NULL)
    {
        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, FALSE);
    }

    function authorized($module, $subpermission=NULL, $item_id=NULL, $itemname=NULL)
    {
        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, TRUE);
    }

    function allowedItem($module, $item_id, $itemname=NULL)
    {
        return $_SESSION['User']->allowedItem($module, $item_id, $itemname);
    }

    function deityAllow()
    {
        return $_SESSION['User']->deityAllow();
    }

    function disallow($message=NULL)
    {
        PHPWS_User::disallow($message);
    }

    function getLogin()
    {
        PHPWS_Core::initModClass('users', 'Form.php');
        $login = User_Form::logBox();
        if (!empty($login)) {
            Layout::set($login, 'users', 'login_box', FALSE);
        }
    }

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
        if (!isset($_SESSION['User']))
            return NULL;
        return $_SESSION['User']->getAuthKey();
    }

    function verifyAuthKey()
    {
        return $_SESSION['User']->verifyAuthKey();
    }

    function isRestricted($module)
    {
        if (Current_User::isDeity())
            return FALSE;
     
        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == RESTRICTED_PERMISSION ? TRUE : FALSE;
    }

    function isUnrestricted($module)
    {
        if (Current_User::isDeity()) {
            return TRUE;
        }
        if (empty($module)) {
            return FALSE;
        }

        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == UNRESTRICTED_PERMISSION ? TRUE : FALSE;
    }

    function updateLastLogged()
    {
        $db = & new PHPWS_DB('users');
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

    function isLogged()
    {
        if (!isset($_SESSION['User'])) {
            $_SESSION['User'] = & new PHPWS_User;
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

    function giveItemPermission($module, $item_id, $itemname)
    {
        return Users_Permission::giveItemPermission(Current_User::getId(), $module, $item_id, $itemname);
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
            return NULL;
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
                $links[] = Current_User::popupPermission($key->id, sprintf(_('Set permissions'), $key->title));
                MiniAdmin::add('users', $links);
            }
        }
    }

    function popupPermission($key_id, $label=NULL)
    {
        if (empty($label)) {
            $js_vars['label'] = _('Permission');
        } else {
            $js_vars['label'] = strip_tags($label);
        }

        $js_vars['width'] = 350;
        $js_vars['height'] = 325;

        $js_vars['address'] = sprintf('index.php?module=users&action=popup_permission&key_id=%s&authkey=%s',$key_id, Current_User::getAuthKey());

        return javascript('open_window', $js_vars);
    }


    function loginUser($username, $password)
    {
        $username = preg_replace('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', '', $username);
        $createUser = FALSE;
        // First check if they are currently a user in local system
        $user = & new PHPWS_User;

        $db = & new PHPWS_DB('users');
        $db->addWhere('username', strtolower($username));
        $result = $db->loadObject($user);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return FALSE;
        }

        // if result is blank then check against the default authorization
        if ($result == FALSE){
            $authorize = PHPWS_User::getUserSetting('default_authorization');
            $createUser = TRUE;
        }
        else {
            $authorize = $user->getAuthorize();
        }

        if (empty($authorize)) {
            return FALSE;
        }

        $result = Current_User::authorize($authorize, $username, $password);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return FALSE;
        }

        if ($result == TRUE){
            if ($createUser == TRUE){
                $result = $user->setUsername($username);

                if (PEAR::isError($result)){
                    PHPWS_Error::log($result);
                    return FALSE;
                }

                $user->setAuthorize($authorize);
                $user->setActive(TRUE);
                $user->setApproved(TRUE);

                if (function_exists('post_authorize')) {
                    post_authorize($user);
                }

                $user->save();
            }

            $user->login();
            $_SESSION['User'] = $user;
            return TRUE;
        } else
            return FALSE;
    }

    function authorize($authorize, $username, $password)
    {
        $db = & new PHPWS_DB('users_auth_scripts');
        $db->setIndexBy('id');
        $result = $db->select();

        if (empty($result)) {
            return FALSE;
        }

        if (isset($result[$authorize])) { 
            extract($result[$authorize]);
            $file = PHPWS_SOURCE_DIR . 'mod/users/scripts/' . $filename;
            if(!is_file($file)){
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize', $file);
                return FALSE;
            }

            include $file;
            if (function_exists('authorize')){
                $result = authorize($username, $password);
                return $result;
            } else {
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize');
                return FALSE;
            }
        } else
            return FALSE;

        return $result;
    }


}

?>