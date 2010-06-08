<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// Destroy unused sessions
if (core\Core::getCurrentModule() != 'users'){
    \core\Core::killSession('Member_Pager');
    \core\Core::killSession('All_Demo');
    \core\Core::killSession('User_Manager');
    \core\Core::killSession('Group_Manager');
}

Current_User::permissionMenu();
?>