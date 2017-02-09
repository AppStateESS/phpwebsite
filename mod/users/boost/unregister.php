<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function users_unregister($module, &$content){
    \phpws\PHPWS_Core::initModClass('users', 'Permission.php');
    \phpws\PHPWS_Core::initModClass('users', 'My_Page.php');
    $result = Users_Permission::removePermissions($module);

    if (PHPWS_Error::isError($result)) {

        $content[] = 'Permissions table not removed successfully.';

        return FALSE;
    } elseif ($result) {
        $content[] = 'Permissions table removed successfully.';
    }

    $result = My_Page::unregisterMyPage($module);
    if (PHPWS_Error::isError($result)){
        PHPWS_Boost::addLog('users', 'A problem occurred when trying to unregister this module from My Page.');
        $content[] = 'A problem occurred when trying to unregister this module from My Page.';
        return FALSE;
    } elseif ($result != FALSE) {
        $content[] = 'My Page unregistered from Users module.';
    }

    return TRUE;
}
