<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function users_register($module, &$content)
{
    PHPWS_Core::initModClass('users', 'Permission.php');
    PHPWS_Core::initModClass('users', 'My_Page.php');

    $no_permissions = $no_my_page = FALSE;

    $result = Users_Permission::createPermissions($module);

    if (is_null($result)){
        PHPWS_Boost::addLog('users', dgettext('users', 'Permissions file not implemented.'));
        $content[] =  dgettext('users', 'Permissions file not implemented.');
        $no_permissions = TRUE;
    } elseif (PHPWS_Error::isError($result)) {
        $content[] = dgettext('users', 'Permissions table not created successfully.');
        PHPWS_Error::log($result);
        return FALSE;
    } else {
        $content[] = dgettext('users', 'Permissions table created successfully.');
    }

    $result = My_Page::registerMyPage($module);
    if (PHPWS_Error::isError($result)){
        PHPWS_Boost::addLog('users', dgettext('users', 'A problem occurred when trying to register this module to My Page.'));
        $content[] = dgettext('users', 'A problem occurred when trying to register this module to My Page.');
        return FALSE;
    } elseif ($result != FALSE) {
        $content[] = dgettext('users', 'My Page registered to Users module.');
    } else {
        $no_my_page = TRUE;
    }

    // If the module doesn't have permissions or a My Page
    // then don't register the module
    if ($no_permissions && $no_my_page) {
        return FALSE;
    } else {
        return TRUE;
    }
}

?>
