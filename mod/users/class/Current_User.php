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
        Layout::set($login, 'users', 'login_box', FALSE);
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
        if (Current_User::isDeity())
            return TRUE;
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
        if (!isset($_SESSION['User']))
            return FALSE;

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

}

?>