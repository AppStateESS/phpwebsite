<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function users_unregister($module, &$content){
    \core\Core::initModClass('users', 'Permission.php');
    \core\Core::initModClass('users', 'My_Page.php');
    $result = Users_Permission::removePermissions($module);

    if (core\Error::isError($result)) {

        $content[] = dgettext('users', 'Permissions table not removed successfully.');

        return FALSE;
    } elseif ($result) {
        $content[] = dgettext('users', 'Permissions table removed successfully.');
    }

    $result = My_Page::unregisterMyPage($module);
    if (core\Error::isError($result)){
        PHPWS_Boost::addLog('users', dgettext('users', 'A problem occurred when trying to unregister this module from My Page.'));
        $content[] = dgettext('users', 'A problem occurred when trying to unregister this module from My Page.');
        return FALSE;
    } elseif ($result != FALSE) {
        $content[] = dgettext('users', 'My Page unregistered from Users module.');
    }

    return TRUE;
}


?>