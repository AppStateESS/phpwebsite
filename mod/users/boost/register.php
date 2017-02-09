<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function users_register($module, &$content)
{
    \phpws\PHPWS_Core::initModClass('users', 'Permission.php');
    \phpws\PHPWS_Core::initModClass('users', 'My_Page.php');

    $no_permissions = $no_my_page = FALSE;

    $result = Users_Permission::createPermissions($module);

    if (is_null($result)){
        PHPWS_Boost::addLog('users', 'Permissions file not implemented.');
        $content[] =  'Permissions file not implemented.';
        $no_permissions = TRUE;
    } elseif (PHPWS_Error::isError($result)) {
        $content[] = 'Permissions table not created successfully.';
        PHPWS_Error::log($result);
        return FALSE;
    } else {
        $content[] = 'Permissions table created successfully.';
    }

    $result = My_Page::registerMyPage($module);
    if (PHPWS_Error::isError($result)){
        PHPWS_Boost::addLog('users', 'A problem occurred when trying to register this module to My Page.');
        $content[] = 'A problem occurred when trying to register this module to My Page.';
        return FALSE;
    } elseif ($result != FALSE) {
        $content[] = 'My Page registered to Users module.';
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

